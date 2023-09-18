<?php

use BeechIt\FalSecuredownload\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

if (!ExtensionConfiguration::trackDownloads()) {
    return;
}

$additionalColumns = [
    'downloads' => [
        'exclude' => true,
        'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:downloadStatistics.label',
        'config' => [
            'type' => 'input',
            'renderType' => 'falSecureDownloadStats',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('fe_users', $additionalColumns);
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'downloads');
