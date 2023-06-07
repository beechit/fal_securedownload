<?php

declare(strict_types=1);

/*
 *  Copyright notice
 *
 *  (c) 2014 Frans Saris <frans@beech.it>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Hooks called after a record is added/updated/deleted
 */
class ProcessDatamapHook
{

    /**
     * Trigger updateFolderTree after change in tx_falsecuredownload_folder
     *
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void
    {
        if ($table === 'tx_falsecuredownload_folder') {
            BackendUtility::setUpdateSignal('updateFolderTree');
        }
    }

    /**
     * Trigger updateFolderTree after a sys_file_collection record is deleted
     *
     * @param string $command
     * @param string $table
     * @param string $id
     * @param mixed $value
     * @param DataHandler $dataHandler
     * @param mixed $pasteUpdate
     * @param array $pasteDatamap
     */
    public function processCmdmap_postProcess(
        string $command,
        string $table,
        string $id,
        $value,
        DataHandler $dataHandler,
        $pasteUpdate,
        array $pasteDatamap
    ): void
    {
        if ($table === 'tx_falsecuredownload_folder') {
            BackendUtility::setUpdateSignal('updateFolderTree');
        }
    }
}
