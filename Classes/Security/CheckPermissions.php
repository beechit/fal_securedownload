<?php
namespace BeechIt\FalSecuredownload\Security;

/***************************************************************
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
 ***************************************************************/

use BeechIt\FalSecuredownload\Events\AddCustomGroupsEvent;
use BeechIt\FalSecuredownload\Service\Utility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\Mfa\MfaRequiredException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Utility functions to check permissions
 */
class CheckPermissions implements SingletonInterface
{

    /**
     * @var Utility
     */
    protected $utilityService;

    /**
     * @var array check folder root-line access cache
     */
    protected $checkFolderRootLineAccessCache = [];

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->utilityService = GeneralUtility::makeInstance(Utility::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check file access for current FeUser
     *
     * @param File $file
     * @return bool
     */
    public function checkFileAccessForCurrentFeUser($file)
    {
        $userFeGroups = !isset($GLOBALS['TSFE']->fe_user->user) ? false : $GLOBALS['TSFE']->fe_user->groupData['uid'];
        return $this->checkFileAccess($file, $userFeGroups);
    }

    /**
     * Check backend user file access
     *
     * @param File $file
     * @return bool
     */
    public function checkBackendUserFileAccess(File $file): bool
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        if (!$backendUser instanceof BackendUserAuthentication || empty($backendUser->user['uid'])) {
            return false;
        }
        if ($backendUser->isAdmin()) {
            return true;
        }
        $resourceStorage = $file->getStorage();
        $resourceStorage->setUserPermissions($GLOBALS['BE_USER']->getFilePermissionsForStorage($resourceStorage));
        foreach ($GLOBALS['BE_USER']->getFileMountRecords() as $fileMountRow) {
            if ((int)$fileMountRow['base'] === (int)$resourceStorage->getUid()) {
                try {
                    $resourceStorage->addFileMount($fileMountRow['path'], $fileMountRow);
                } catch (FolderDoesNotExistException $e) {
                    // That file mount does not seem to be valid, fail silently
                }
            }
        }
        $originalEvaluatePermissions = $resourceStorage->getEvaluatePermissions();
        $resourceStorage->setEvaluatePermissions(true);
        $access = $resourceStorage->checkFileActionPermission('read', $file);
        $resourceStorage->setEvaluatePermissions($originalEvaluatePermissions);
        return $access;
    }

    /**
     * Get backend user object
     *
     * @return FrontendBackendUserAuthentication
     * @throws MfaRequiredException
     */
    protected function getBackendUser(): FrontendBackendUserAuthentication
    {
        $backendUserObject = GeneralUtility::makeInstance(FrontendBackendUserAuthentication::class);
        $backendUserObject->start();
        $backendUserObject->unpack_uc();
        if (!empty($backendUserObject->user['uid'])) {
            $backendUserObject->fetchGroupData();
        }
        return $backendUserObject;
    }

    /**
     * Check file access for given FeGroups combination
     *
     * @param File $file
     * @param bool|array $userFeGroups FALSE = no login, array() fe groups of user
     * @return bool
     */
    public function checkFileAccess($file, $userFeGroups)
    {
        // all files in public storage are accessible
        if ($file->getStorage()->isPublic()) {
            return true;
        }

        $customUserGroups = [];
        /** @var AddCustomGroupsEvent $event */
        $event = $this->eventDispatcher->dispatch(new AddCustomGroupsEvent([$customUserGroups]));
        $eventArguments = $event->getCustomUserGroups();
        $customUserGroups = array_shift($eventArguments);

        if (is_array($userFeGroups)) {
            $userFeGroups = array_unique(array_merge($userFeGroups, $customUserGroups));
        }
        if ($userFeGroups === false && !empty($customUserGroups)) {
            $userFeGroups = $customUserGroups;
        }

        /** @var Folder $parentFolder */
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
     * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
     * @return bool
     */
    public function checkFolderRootLineAccess(Folder $folder, $userFeGroups)
    {
        $cacheIdentifier = sha1(
            $folder->getHashedIdentifier() .
            serialize($userFeGroups)
        );

        if (!isset($this->checkFolderRootLineAccessCache[$cacheIdentifier])) {
            $this->checkFolderRootLineAccessCache[$cacheIdentifier] = true;

            // loop through the root line of an folder and check the permissions of every folder
            foreach ($this->getFolderRootLine($folder) as $rootlinefolder) {
                // fetch folder permissions record
                $folderRecord = $this->utilityService->getFolderRecord($rootlinefolder);

                // if record found check permissions
                if ($folderRecord) {
                    if (!$this->matchFeGroupsWithFeUser($folderRecord['fe_groups'], $userFeGroups)) {
                        $this->checkFolderRootLineAccessCache[$cacheIdentifier] = false;
                        break;
                    }
                }
            }
        }
        return $this->checkFolderRootLineAccessCache[$cacheIdentifier];
    }

    /**
     * Get permissions set on folder (no root line check)
     *
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
     * Get FeGroups that are allowed to view a file/folder (checks full rootline)
     *
     * @return string
     */
    public function getPermissions(ResourceInterface $resource)
    {
        $currentPermissionsCheck = $resource->getStorage()->getEvaluatePermissions();
        $resource->getStorage()->setEvaluatePermissions(false);

        $feGroups = [];
        // Check file record for permissions
        if ($resource instanceof File && $resource->getProperty('fe_groups')) {
            $feGroups = GeneralUtility::intExplode(',', $resource->getProperty('fe_groups'), true);
        }

        // If file record does not have permissions set, check folders
        if ($feGroups === []) {
            $folders = array_reverse($this->getFolderRootLine($resource->getParentFolder()));
            foreach ($folders as $folder) {
                $folderRecord = $this->utilityService->getFolderRecord($folder);
                if ($folderRecord && !empty($folderRecord['fe_groups'])) {
                    $feGroups = GeneralUtility::intExplode(',', $folderRecord['fe_groups'], true);

                    if ($feGroups !== []) {
                        break;
                    }
                }
            }
        }

        $resource->getStorage()->setEvaluatePermissions($currentPermissionsCheck);

        return implode(',', $feGroups);
    }

    /**
     * Get all folders in root line of given folder
     *
     * @return Folder[]
     */
    public function getFolderRootLine(FolderInterface $folder)
    {
        $rootLine = [$folder];
        /** @var $parentFolder \TYPO3\CMS\Core\Resource\Folder */
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
        return array_reverse($rootLine);
    }

    /**
     * Check if given groups match with the groups of a user
     *
     * @param string $groups
     * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
     * @return bool
     */
    public function matchFeGroupsWithFeUser($groups, $userFeGroups)
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
}
