<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Markus Klein <markus.klein@reelworx.at>
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

namespace BeechIt\FalSecuredownload\FormEngine;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Lang\LanguageService;

class DownloadStatistics extends AbstractNode
{
    /**
     * @var array
     */
    protected $resultArray = array();

    /**
     * @return array
     */
    public function render()
    {
        $this->resultArray = $this->initializeResultArray();
        $row = $this->data['databaseRow'];

        if ((int)$row['uid'] !== $row['uid']) {
            return $this->resultArray;
        }

        $db = $this->getDatabase();
        $statistics = $db->exec_SELECTgetRows(
            'sys_file.name, count(tx_falsecuredownload_download.file) as cnt',
            'sys_file JOIN tx_falsecuredownload_download ON tx_falsecuredownload_download.file = sys_file.uid',
            'tx_falsecuredownload_download.feuser = ' . $row['uid']
        );

        $lang = $this->getLanguageService();
        $titleFileName = $lang->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:downloadStatistics.fileName');
        $titleDownloads = $lang->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:downloadStatistics.downloads');

        $markup = array();
        $markup[] = '<table class="table table-bordered">';
        $markup[] = '<thead><tr><th>' . htmlspecialchars($titleFileName) . '</th><th>' . htmlspecialchars($titleDownloads) . '</th></tr></thead>';
        $markup[] = '<tbody>';
        foreach ($statistics as $file) {
            $markup[] = '<tr><td>' . htmlspecialchars($file['name']) . '</td><td>' . htmlspecialchars($file['cnt']) . '</td></tr>';
        }
        $markup[] = '</tbody>';
        $markup[] = '</table>';

        $this->resultArray['html'] = implode(LF, $markup);

        return $this->resultArray;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
