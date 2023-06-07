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

use Exception;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to display verbose information about fileTree plugin in Web>Page module
 */
class CmsLayout
{

    protected ResourceFactory $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Flexform information
     */
    public array $flexformData = [];

    /**
     * Returns information about this extension's pi1 plugin
     *
     * Registered as "Page module hook" in ext_localconf.php
     *
     * @param array $params Parameters to the hook
     * @return string Information about pi1 plugin
     * @noinspection PhpUnused
     */
    public function getExtensionSummary(array $params): string
    {
        $tableData = [];
        $result = '<u><strong>' . $this->sL('plugin.title') . '</strong></u>';

        if ($params['row']['list_type'] === 'falsecuredownload_filetree') {
            $this->flexformData = GeneralUtility::xml2array($params['row']['pi_flexform']);

            // Storage
            $storageName = '';
            try {
                $storageUid = $this->getFieldFromFlexform('settings.storage');
                $storageName = $this->resourceFactory->getStorageObject($storageUid)->getName();
            } catch (Exception $exception) {
            }

            if ($storageName) {
                $tableData[] = [
                    $this->sL('flexform.storage'),
                    $storageName
                ];
            }

            // Folder
            $folder = $this->getFieldFromFlexform('settings.folder');
            $tableData[] = [
                $this->sL('flexform.folder'),
                $folder
            ];

            $result .= $this->renderSettingsAsTable($tableData);
            $result = '<div style="background-color:#f1f1f1; padding:8px; margin-top:8px" class="t3-page-ce-info">' . $result . '</div>';
        }

        return $result;
    }

    /**
     * Render the settings as table for Web>Page module
     * System settings are displayed in mono font
     */
    protected function renderSettingsAsTable(array $tableData): string
    {
        if (count($tableData) == 0) {
            return '';
        }

        $content = '';
        foreach ($tableData as $line) {
            $content .= '<tr><td><em><strong>' . $line[0] . '</strong></em></td><td>&nbsp; ' . ' ' . $line[1] . '</td></tr>';
        }

        return '<table style="margin-top: 4px;">' . $content . '</table>';
    }

    /**
     * Get field value from flexform configuration,
     * including checks if flexform configuration is available
     *
     * @param string $key name of the key
     * @param string $sheet name of the sheet
     * @return string|null if nothing found, value if found
     */
    protected function getFieldFromFlexform(string $key, string $sheet = 'sDEF'): ?string
    {
        $flexform = $this->flexformData;
        if (isset($flexform['data'])) {
            $flexform = $flexform['data'];
            if (is_array($flexform) && is_array($flexform[$sheet]) && is_array($flexform[$sheet]['lDEF'])
                && is_array($flexform[$sheet]['lDEF'][$key]) && isset($flexform[$sheet]['lDEF'][$key]['vDEF'])
            ) {
                return $flexform[$sheet]['lDEF'][$key]['vDEF'];
            }
        }
        return null;
    }

    /**
     * Get language string
     */
    protected function sL(string $key): string
    {
        return $this->getLangService()->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:' . $key);
    }

    protected function getLangService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
