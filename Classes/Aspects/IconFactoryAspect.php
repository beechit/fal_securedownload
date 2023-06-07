<?php

declare(strict_types=1);

/*
 *  Copyright notice
 *
 *  (c) 2017 Frans Saris <frans@beech.it>
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

namespace BeechIt\FalSecuredownload\Aspects;

use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconFactoryAspect
{

    public function buildIconForResource(
        ResourceInterface $resource,
        string $size,
        array $options,
        string $iconIdentifier,
        ?string $overlayIdentifier
    ): array
    {
        if (!$resource->getStorage()->isPublic()) {
            /** @var $checkPermissionsService CheckPermissions */
            $checkPermissionsService = GeneralUtility::makeInstance(CheckPermissions::class);

            $currentPermissionsCheck = $resource->getStorage()->getEvaluatePermissions();
            $resource->getStorage()->setEvaluatePermissions(false);

            try {
                $folder = $resource instanceof Folder ? $resource : $resource->getParentFolder();

                if ($resource instanceof File && $resource->getProperty('fe_groups')) {
                    $overlayIdentifier = 'overlay-restricted';

                    // check if there are permissions set on this specific folder
                } elseif ($folder === $resource && $checkPermissionsService->getFolderPermissions($folder) !== false) {
                    $overlayIdentifier = 'overlay-restricted';

                    // check if there are access restrictions in the root line of this folder
                } elseif (!$checkPermissionsService->checkFolderRootLineAccess($folder, false)) {
                    $overlayIdentifier = 'overlay-inherited-permissions';
                }

            } catch (FolderDoesNotExistException $e) {
                // $resource->getParentFolder() may throw a FolderDoesNotExistException which currently is not documented in PHPDoc
            }

            $resource->getStorage()->setEvaluatePermissions($currentPermissionsCheck);
        }
        return [$resource, $size, $options, $iconIdentifier, $overlayIdentifier];
    }
}
