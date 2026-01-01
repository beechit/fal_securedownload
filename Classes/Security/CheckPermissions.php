<?php

declare(strict_types=1);

/*
 *  Copyright notice
 *
 *  (c) 2014 Frans Saris <frans@beech.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace BeechIt\FalSecuredownload\Security;

use BeechIt\FalSecuredownload\Events\AddCustomGroupsEvent;
use BeechIt\FalSecuredownload\Service\Utility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility functions to check permissions
 */
class CheckPermissions implements SingletonInterface
{
    protected Utility $utilityService;
    protected array $checkFolderRootLineAccessCache = [];
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->utilityService = GeneralUtility::makeInstance(Utility::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check file access for current FeUser
     *
     * TODO: check if meant to be public api, otherwise remove
     *
     * @noinspection PhpUnused
     */
    public function checkFileAccessForCurrentFeUser(FileInterface $file): bool
    {
        $userFeGroups = !isset($GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->user) ? false : $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->groupData['uid'];
        try {
            return $this->checkFileAccess($file, $userFeGroups);
        } catch (FolderDoesNotExistException) {
            return false;
        }
    }

    /**
     * Check backend user file access
     */
    public function checkBackendUserFileAccess(FileInterface $file): bool
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        if (!$backendUser instanceof BackendUserAuthentication || empty($backendUser->user['uid'])) {
            return false;
        }
        if ($backendUser->isAdmin()) {
            return true;
        }

        $resourceStorage = $file->getStorage();

        $finalUserPermissions = $backendUser->getFilePermissions();
        $storageFilePermissions = $backendUser->getTSConfig()['permissions.']['file.']['storage.'][$resourceStorage->getUid() . '.'] ?? [];
        if (!empty($storageFilePermissions)) {
            array_walk(
                $storageFilePermissions,
                static function (string $value, string $permission) use (&$finalUserPermissions): void {
                    $finalUserPermissions[$permission] = (bool)$value;
                }
            );
        }

        foreach ($backendUser->getFileMountRecords() as $fileMountRecord) {
            if (!str_contains($fileMountRecord['identifier'], ':')) {
                continue;
            }

            [$base, $path] = GeneralUtility::trimExplode(':', $fileMountRecord['identifier']);
            if ((int)$base !== $resourceStorage->getUid()) {
                continue;
            }

            try {
                $resourceStorage->addFileMount($path, $fileMountRecord);
            } catch (FolderDoesNotExistException) {
                // That file mount does not seem to be valid, fail silently
            }
        }
        $resourceStorage->setUserPermissions($finalUserPermissions);
        $originalEvaluatePermissions = $resourceStorage->getEvaluatePermissions();
        $resourceStorage->setEvaluatePermissions(true);
        $access = $resourceStorage->checkFileActionPermission('read', $file);
        $resourceStorage->setEvaluatePermissions($originalEvaluatePermissions);
        return $access;
    }

    /**
     * Check file access for given FeGroups combination
     *
     * @param FileInterface $file
     * @param bool|array $userFeGroups FALSE = no login, array() fe groups of user
     * @return bool
     * @throws FolderDoesNotExistException
     */
    public function checkFileAccess(FileInterface $file, $userFeGroups): bool
    {
        // all files in public storage are accessible
        if ($file->getStorage()->isPublic()) {
            return true;
        }

        $customUserGroups = [];
        $addCustomGroupsEvent = $this->eventDispatcher->dispatch(new AddCustomGroupsEvent([$customUserGroups]));
        $eventArguments = $addCustomGroupsEvent->getCustomUserGroups();
        $customUserGroups = array_shift($eventArguments);

        if (is_array($userFeGroups)) {
            $userFeGroups = array_unique(array_merge($userFeGroups, $customUserGroups));
        }
        if ($userFeGroups === false && !empty($customUserGroups)) {
            $userFeGroups = $customUserGroups;
        }

        // $file->getParentFolder() may throw a FolderDoesNotExistException which currently is not documented in PHPDoc
        $parentFolder = $file->getParentFolder();
        // check folder access
        if ($this->checkFolderRootLineAccess($parentFolder, $userFeGroups)) {
            // access to folder then check file privileges if present
            $feGroups = $file->getProperty('fe_groups');
            if ((string)$feGroups !== '') {
                return $this->matchFeGroupsWithFeUser($feGroups, $userFeGroups);
            }
            return true;
        }
        return false;
    }

    /**
     * Check if given FeGroups have enough rights to access given folder
     *
     * @param FolderInterface $folder
     * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
     * @return bool
     */
    public function checkFolderRootLineAccess(FolderInterface $folder, $userFeGroups): bool
    {
        $cacheIdentifier = sha1(
            $folder->getHashedIdentifier() .
            $folder->getStorage()->getUid() .
            serialize($userFeGroups)
        );

        if (!isset($this->checkFolderRootLineAccessCache[$cacheIdentifier])) {
            $this->checkFolderRootLineAccessCache[$cacheIdentifier] = true;

            // loop through the root line of a folder and check the permissions of every folder
            try {
                foreach ($this->getFolderRootLine($folder) as $rootlineFolder) {
                    // fetch folder permissions record
                    $folderRecord = $this->utilityService->getFolderRecord($rootlineFolder);

                    // if record found check permissions
                    if ($folderRecord) {
                        if (!$this->matchFeGroupsWithFeUser($folderRecord['fe_groups'], $userFeGroups)) {
                            $this->checkFolderRootLineAccessCache[$cacheIdentifier] = false;
                            break;
                        }
                    }
                }
            } catch (FolderDoesNotExistException) {
                return false;
            }
        }
        return $this->checkFolderRootLineAccessCache[$cacheIdentifier];
    }

    /**
     * Get permissions set on folder (no root line check)
     *
     * @param FolderInterface $folder
     * @return bool|string FALSE or comma separated list of fe_group uids
     */
    public function getFolderPermissions(FolderInterface $folder)
    {
        $permissions = false;
        $folderRecord = $this->utilityService->getFolderRecord($folder);
        if ($folderRecord) {
            $permissions = $folderRecord['fe_groups'] ?: false;
        }
        return $permissions;
    }

    /**
     * Get FeGroups that are allowed to view a file/folder (checks NOT full rootline)
     * Check from the given folder up to root, i. e. the reverse! rootline.
     * First restriction matches.
     */
    public function getPermissions(ResourceInterface $resource): string
    {
        $currentPermissionsCheck = $resource->getStorage()->getEvaluatePermissions();
        $resource->getStorage()->setEvaluatePermissions(false);

        $feGroups = [];
        // loop through the root line of a folder and check the permissions of every folder
        try {
            foreach ($this->getReverseFolderRootLine($resource->getParentFolder()) as $folder) {
                // fetch folder permissions record
                $folderRecord = $this->utilityService->getFolderRecord($folder);

                // if record found check permissions
                if ($folderRecord) {
                    if ($feGroups === []) {
                        $feGroups = GeneralUtility::trimExplode(',', $folderRecord['fe_groups'], true);
                    }
                    if ($folderRecord['fe_groups']) {
                        $feGroups = ArrayUtility::keepItemsInArray($feGroups, $folderRecord['fe_groups']);
                    }
                    if (count($feGroups)) {
                        // only stop searching if something was found
                        $parentGroups = $this->fetchParentGroupsRecursive($feGroups);
                        $feGroups = array_merge($feGroups, $parentGroups);
                        break;
                    }
                }
            }
        } catch (FolderDoesNotExistException) {
        }
        if ($resource instanceof FileInterface && $resource->getProperty('fe_groups')) {
            $resourceGroups = GeneralUtility::trimExplode(',', $resource->getProperty('fe_groups'));
            $resourceParentGroups = $this->fetchParentGroupsRecursive($resourceGroups);
            $resourceGroups = array_merge($resourceGroups, $resourceParentGroups);
            
            if (count($feGroups)) {
                $feGroups = ArrayUtility::keepItemsInArray($feGroups, $resourceGroups);
                // @todo: if $feGroups is empty now and was not before, ensure nobody has access
                // But solrfal will kick out any nonexisting group. So we leave as it is for now
            } else {
                // if no frontend groups were found in rootline overtake file properties
                $feGroups = $resourceGroups;
            }
        }
        $resource->getStorage()->setEvaluatePermissions($currentPermissionsCheck);
        return implode(',', $feGroups);
    }

    /**
     * Get all folders in root line of given folder in reverse order
     *
     * @return FolderInterface[]
     * @throws FolderDoesNotExistException
     */
    public function getReverseFolderRootLine(FolderInterface $folder): array
    {
        $rootLine = [$folder];
        // $folder->getParentFolder() may throw a FolderDoesNotExistException which currently is not documented in PHPDoc
        $parentFolder = $folder->getParentFolder();
        $count = 0;
        while ($parentFolder->getIdentifier() !== $folder->getIdentifier()) {
            $rootLine[] = $parentFolder;
            $count++;
            if ($count > 999) {
                break;
            }
            $folder = $parentFolder;
            $parentFolder = $parentFolder->getParentFolder();
        }
        return $rootLine;
    }

    /**
     * Get all folders in root line of given folder
     *
     * @return Folder[]
     */
    public function getFolderRootLine(FolderInterface $folder)
    {
        return array_reverse($this->getReverseFolderRootLine($folder));
    }

    /**
     * Check if given groups match with the groups of a user
     *
     * @param string $groups
     * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
     * @return bool
     */
    public function matchFeGroupsWithFeUser(string $groups, $userFeGroups): bool
    {
        // no groups specified everyone has access
        if ($groups === '') {
            return true;
        }

        // no login then no access
        if ($userFeGroups === false) {
            return false;
        }

        // enabled for all loggedIn Users
        if (str_contains($groups, '-2')) {
            return true;
        }

        // user not member of any group then no access
        if (!is_array($userFeGroups)) {
            return false;
        }

        foreach (explode(',', $groups) as $feGroupUid) {
            if (in_array(trim($feGroupUid), $userFeGroups)) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Load a list of group uids, and take into account if groups have been loaded before as part of recursive detection.
     * This method very is similar to that in \TYPO3\CMS\Core\Authentication\GroupResolver, but that one is protected
     *
     * @param int[] $groupIds a list of groups to find THEIR ancestors
     * @param array $processedGroupIds helper function to avoid recursive detection
     * @return array a list of parent groups and thus, grand grand parent groups as well
     */
    protected function fetchParentGroupsRecursive(array $groupIds, array $processedGroupIds = []): array
    {
        if (empty($groupIds)) {
            return [];
        }
        $parentGroups = $this->fetchParentGroupsFromDatabase($groupIds);
        $validParentGroupIds = [];
        foreach ($parentGroups as $parentGroup) {
            $parentGroupId = (int)$parentGroup['uid'];
            // Record was already processed, continue to avoid adding this group again
            if (in_array($parentGroupId, $processedGroupIds, true)) {
                continue;
            }
            $processedGroupIds[] = $parentGroupId;
            $validParentGroupIds[] = $parentGroupId;
        }

        $grandParentGroups = $this->fetchParentGroupsRecursive($validParentGroupIds, $processedGroupIds);
        return array_merge($validParentGroupIds, $grandParentGroups);
    }

    /**
     * Find all groups that have a FIND_IN_SET(subgroups, [$subgroupIds]) => the parent groups
     * via one SQL query.
     * This method very is similar to that in \TYPO3\CMS\Core\Authentication\GroupResolver, but that one is protected
     */
    protected function fetchParentGroupsFromDatabase(array $subgroupIds): array
    {
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('fe_groups');
        $queryBuilder
            ->select('*')
            ->from('fe_groups');

        $constraints = [];
        foreach ($subgroupIds as $subgroupId) {
            $constraints[] = $queryBuilder->expr()->inSet('subgroup', (string)$subgroupId);
        }

        $result = $queryBuilder
            ->where(
                $queryBuilder->expr()->or(...$constraints)
            )
            ->executeQuery();

        $groups = [];
        while ($row = $result->fetchAssociative()) {
            $groups[(int)$row['uid']] = $row;
        }
        return $groups;
    }

}
