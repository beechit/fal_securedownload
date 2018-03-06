<?php
namespace BeechIt\FalSecuredownload\Aspects;

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

use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PublicUrlAspect
 */
class PublicUrlAspect implements SingletonInterface
{

    /**
     * Flag to en-/disable rendering of BE user link instead of FE link
     *
     * @var bool
     */
    protected $enabled = true;

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Generate public url for file
     *
     * @param Resource\ResourceStorage $storage
     * @param Resource\Driver\DriverInterface $driver
     * @param Resource\ResourceInterface $resourceObject
     * @param $relativeToCurrentScript
     * @param array $urlData
     * @return void
     */
    public function generatePublicUrl(
        Resource\ResourceStorage $storage,
        Resource\Driver\DriverInterface $driver,
        Resource\ResourceInterface $resourceObject,
        $relativeToCurrentScript,
        array $urlData
    ) {

        // We only render special links for non-public files
        if ($this->enabled && $resourceObject instanceof Resource\FileInterface && !$storage->isPublic()) {
            $queryParameterArray = ['eID' => 'dumpFile', 't' => ''];
            if ($resourceObject instanceof Resource\File) {
                $queryParameterArray['f'] = $resourceObject->getUid();
                $queryParameterArray['t'] = 'f';
            } elseif ($resourceObject instanceof Resource\ProcessedFile) {
                $queryParameterArray['p'] = $resourceObject->getUid();
                $queryParameterArray['t'] = 'p';
            }
            $queryParameterArray['token'] = GeneralUtility::hmac(
                implode('|', $queryParameterArray),
                'BeResourceStorageDumpFile'
            );

            // $urlData['publicUrl'] is passed by reference, so we can change that here and the value will be taken into account
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $urlData['publicUrl'] = (string) $uriBuilder->buildUriFromRoute(
                'ajax_dump_file',
                $queryParameterArray,
                UriBuilder::ABSOLUTE_URL
            );
        }
    }
}
