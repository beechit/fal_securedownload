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

namespace BeechIt\FalSecuredownload\Hooks;

use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class KeSearchFilesHook implements SingletonInterface
{
    protected CheckPermissions $checkPermissionsService;

    public function __construct()
    {
        $this->checkPermissionsService = GeneralUtility::makeInstance(CheckPermissions::class);
    }

    /**
     * Check file permissions
     *
     * @param $fileObject
     * @param string $content
     * @param \tx_kesearch_indexer_types_file $fileIndexerObject
     * @param string $feGroups
     * @param array $ttContentRow
     * @param int $storagePid
     * @param string $title
     * @param string $tags
     * @param string $abstract
     * @param array $additionalFields
     */
    public function modifyFileIndexEntryFromContentIndexer(
        $fileObject,
        string $content,
        \tx_kesearch_indexer_types_file $fileIndexerObject,
        string &$feGroups,
        array $ttContentRow,
        int $storagePid,
        string $title,
        string $tags,
        string $abstract,
        array $additionalFields
    ): void {
        if ($fileObject instanceof File && !$fileObject->getStorage()->isPublic()) {
            $resourcePermissions = $this->checkPermissionsService->getPermissions($fileObject);
            // If there are already permissions set, refine these with actual file permissions
            if ($feGroups) {
                $feGroups = implode(
                    ',',
                    ArrayUtility::keepItemsInArray(explode(',', $resourcePermissions), $feGroups)
                );
            } else {
                $feGroups = $resourcePermissions;
            }
        }
    }

    /**
     * Get user permissions
     *
     * @param string|File $file
     * @param string $content
     * @param array $additionalFields
     * @param array $indexRecordValues
     * @param \tx_kesearch_indexer_types_file $indexer
     */
    public function modifyFileIndexEntry(
        $file,
        string $content,
        array $additionalFields,
        array &$indexRecordValues,
        \tx_kesearch_indexer_types_file $indexer
    ): void {
        if ($file instanceof File && !$file->getStorage()->isPublic()) {
            $indexRecordValues['fe_group'] = $this->checkPermissionsService->getPermissions($file);
        }
    }
}
