<?php
namespace BeechIt\FalSecuredownload\EventListener;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Frans Saris <frans@beech.it>
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

use BeechIt\FalSecuredownload\Service\Utility;
use TYPO3\CMS\Core\Resource\Event\AfterFolderDeletedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFolderMovedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFolderRenamedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFolderDeletedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFolderMovedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFolderRenamedEvent;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Slots that pick up signals after (re)moving folders to update folder record
 */
class FolderChangedEventListener implements SingletonInterface
{
    protected $folderMapping = [];

    /**
     * @var Utility
     */
    protected $utilityService;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->utilityService = GeneralUtility::makeInstance(Utility::class);
    }

    /**
     * Get sub folder structure of folder before is gets moved
     * Is needed to update folder records when move was successful
     */
    public function preFolderMove(BeforeFolderMovedEvent $event)
    {
        $this->folderMapping[$event->getFolder()->getCombinedIdentifier()] = $this->getSubFolderIdentifiers($event->getFolder());
    }

    /**
     * Update folder permissions records when folder is moved
     */
    public function postFolderMove(AfterFolderMovedEvent $event)
    {
        $folder = $event->getFolder();
        $newFolder = $event->getTargetFolder()->getSubfolder($event->getTargetFolder()->getName());
        $oldStorageUid = $folder->getStorage()->getUid();
        $newStorageUid = $newFolder->getStorage()->getUid();

        $this->utilityService->updateFolderRecord(
            $oldStorageUid,
            $folder->getHashedIdentifier(),
            $folder->getIdentifier(),
            [
                'storage' => $newStorageUid,
                'folder_hash' => $newFolder->getHashedIdentifier(),
                'folder' => $newFolder->getIdentifier()
            ]
        );

        if (!empty($this->folderMapping[$folder->getCombinedIdentifier()])) {
            $newMapping = $this->getSubFolderIdentifiers($newFolder);
            foreach ($this->folderMapping[$folder->getCombinedIdentifier()] as $key => $folderInfo) {
                $this->utilityService->updateFolderRecord(
                    $oldStorageUid,
                    $folderInfo[0],
                    $folderInfo[1],
                    [
                        'storage' => $newStorageUid,
                        'folder_hash' => $newMapping[$key][0],
                        'folder' => $newMapping[$key][1]
                    ]
                );
            }
        }
    }

    /**
     * Get sub folder structure of folder before is gets deleted
     * Is needed to update folder records when delete was successful
     */
    public function preFolderDelete(BeforeFolderDeletedEvent $event)
    {
        $this->folderMapping[$event->getFolder()->getCombinedIdentifier()] = $this->getSubFolderIdentifiers($event->getFolder());
    }

    /**
     * Update folder permissions records when folder is deleted
     */
    public function postFolderDelete(AfterFolderDeletedEvent $event)
    {
        $folder = $event->getFolder();
        $storageUid = $folder->getStorage()->getUid();
        $this->utilityService->deleteFolderRecord(
            $storageUid,
            $folder->getHashedIdentifier(),
            $folder->getIdentifier()
        );
        foreach ($this->folderMapping[$folder->getCombinedIdentifier()] as $folderInfo) {
            $this->utilityService->deleteFolderRecord($storageUid, $folderInfo[0], $folderInfo[1]);
        }
    }

    /**
     * Get sub folder structure of folder before is gets renamed
     * Is needed to update folder records when renaming was successful
     */
    public function preFolderRename(BeforeFolderRenamedEvent $event)
    {
        $this->folderMapping[$event->getFolder()->getCombinedIdentifier()] = $this->getSubFolderIdentifiers($event->getFolder());
    }

    /**
     * Update folder permissions records when a folder is renamed
     */
    public function postFolderRename(AfterFolderRenamedEvent $event)
    {
        $folder = $event->getSourceFolder();
        $newFolder = $event->getFolder();
        $oldStorageUid = $folder->getStorage()->getUid();
        $newStorageUid = $newFolder->getStorage()->getUid();

        $this->utilityService->updateFolderRecord(
            $oldStorageUid,
            $folder->getHashedIdentifier(),
            $folder->getIdentifier(),
            [
                'storage' => $newStorageUid,
                'folder_hash' => $newFolder->getHashedIdentifier(),
                'folder' => $newFolder->getIdentifier()
            ]
        );

        if (!empty($this->folderMapping[$folder->getCombinedIdentifier()])) {
            $newMapping = $this->getSubFolderIdentifiers($newFolder);
            foreach ($this->folderMapping[$folder->getCombinedIdentifier()] as $key => $folderInfo) {
                $this->utilityService->updateFolderRecord(
                    $oldStorageUid,
                    $folderInfo[0],
                    $folderInfo[1],
                    [
                        'storage' => $newStorageUid,
                        'folder_hash' => $newMapping[$key][0],
                        'folder' => $newMapping[$key][1]
                    ]
                );
            }
        }
    }

    /**
     * Get folder
     *
     * @param Folder $folder
     * @return array
     */
    protected function getSubFolderIdentifiers(Folder $folder)
    {
        $folderIdentifiers = [];
        foreach ($folder->getSubfolders() as $subFolder) {
            $folderIdentifiers[] = [$subFolder->getHashedIdentifier(), $subFolder->getIdentifier()];
            $folderIdentifiers = array_merge($folderIdentifiers, $this->getSubFolderIdentifiers($subFolder));
        }
        return $folderIdentifiers;
    }
}
