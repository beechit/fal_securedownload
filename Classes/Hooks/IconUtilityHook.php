<?php
namespace BeechIt\FalSecuredownload\Hooks;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <frans@beech.it>
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
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Folder;
/**
 * IconUtility Hook to add overlay icons when file/folder isn't public
 */
class IconUtilityHook implements \TYPO3\CMS\Backend\Utility\IconUtilityOverrideResourceIconHookInterface
    {
      /**
      * @param \TYPO3\CMS\Core\Resource\ResourceInterface $resource
      * @param $iconName
      * @param array $options
      * @param array $overlays
      */
        public function overrideResourceIcon(\TYPO3\CMS\Core\Resource\ResourceInterface $resource, &$iconName, array &$options, array &$overlays) {
            if (!$resource->getStorage()->isPublic()) {
                /** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
                $checkPermissionsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Security\\CheckPermissions');
                $currentPermissionsCheck = $resource->getStorage()->getEvaluatePermissions();
                $resource->getStorage()->setEvaluatePermissions(FALSE);
                
                if ($resource instanceof \TYPO3\CMS\Core\Resource\Folder) {
                    $folder = $resource;
                } else {
                    $folder = $resource->getParentFolder();
                }
                
                if ($resource instanceof \TYPO3\CMS\Core\Resource\File && $resource->getProperty('fe_groups')) {
                    $overlays['status-overlay-access-restricted'] = array();
                    
                    // check if there are permissions set on this specific folder
                } elseif ($folder === $resource && $checkPermissionsService->getFolderPermissions($folder) !== FALSE) {
                    $overlays['status-overlay-access-restricted'] = array();
                    
                    // check if there are access restrictions in the root line of this folder
                } elseif (!$checkPermissionsService->checkFolderRootLineAccess($folder, FALSE)) {
                    $overlays['extensions-fal_securedownload-overlay-permissions'] = array();
                }
                
                $resource->getStorage()->setEvaluatePermissions($currentPermissionsCheck);
            }
        }
        /** @param ResourceInterface $folderObject
        * @param string $size
        * @param array $options
        * @param string $iconIdentifier
        * @param string $overlayIdentifier
        * @return array
        
        public function buildIconForResource(ResourceInterface $folderObject, $size, array $options, $iconIdentifier, $overlayIdentifier)
        {
            if ($folderObject && $folderObject instanceof Folder
                && in_array($folderObject->getRole(), array(Folder::ROLE_DEFAULT, Folder::ROLE_USERUPLOAD))
                ) {
                $mediaFolders = self::getMediaFolders();
                if (count($mediaFolders)) {
                    /** @var \MiniFranske\FsMediaGallery\Service\Utility $utility
                    $utility = GeneralUtility::makeInstance('MiniFranske\\FsMediaGallery\\Service\\Utility');
                    $collections = $utility->findFileCollectionRecordsForFolder(
                                                                                $folderObject->getStorage()->getUid(),
                                                                                $folderObject->getIdentifier(),
                                                                                array_keys($mediaFolders)
                                                                                );
                    if ($collections) {
                        $iconIdentifier = 'tcarecords-sys_file_collection-folder';
                        $hidden = TRUE;
                        foreach ($collections as $collection) {
                            if ((int)$collection['hidden'] === 0) {
                                $hidden = FALSE;
                                break;
                            }
                        }
                        if ($hidden) {
                            $overlayIdentifier = 'overlay-hidden';
                        }
                    }
                }
            }
            return array($folderObject, $size, $options, $iconIdentifier, $overlayIdentifier);
        }*/
    }