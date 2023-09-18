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

namespace BeechIt\FalSecuredownload\Aspects;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PublicUrlAspect implements SingletonInterface
{
    /**
     * Flag to en-/disable rendering of BE user link instead of FE link
     */
    protected bool $enabled = true;

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Generate public url for file
     *
     * @param ResourceStorage $storage
     * @param DriverInterface $driver
     * @param ResourceInterface $resourceObject
     * @param mixed $relativeToCurrentScript Deprecated. Will be removed in a future version
     * @param array $urlData
     * @throws RouteNotFoundException
     */
    public function generatePublicUrl(
        ResourceStorage $storage,
        DriverInterface $driver,
        ResourceInterface $resourceObject,
        $relativeToCurrentScript,
        array $urlData
    ): void {
        // We only render special links for non-public files
        if ($this->enabled && $resourceObject instanceof FileInterface && !$storage->isPublic()) {
            $queryParameterArray = ['eID' => 'dumpFile', 't' => ''];
            if ($resourceObject instanceof File) {
                $queryParameterArray['f'] = $resourceObject->getUid();
                $queryParameterArray['t'] = 'f';
            } elseif ($resourceObject instanceof ProcessedFile) {
                $queryParameterArray['p'] = $resourceObject->getUid();
                $queryParameterArray['t'] = 'p';
            }
            $queryParameterArray['fal_token'] = GeneralUtility::hmac(
                implode('|', $queryParameterArray),
                'BeResourceStorageDumpFile'
            );

            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            /**
             * $urlData['publicUrl'] is passed by reference, so we can change that here and the value will be taken into account
             * @noinspection PhpArrayWriteIsNotUsedInspection
             * @noinspection PhpArrayUsedOnlyForWriteInspection
             */
            $urlData['publicUrl'] = (string)$uriBuilder->buildUriFromRoute(
                'ajax_dump_file',
                $queryParameterArray,
                UriBuilder::ABSOLUTE_URL
            );
        }
    }
}
