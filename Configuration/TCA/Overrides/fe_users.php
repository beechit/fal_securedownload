<?php
defined('TYPO3_MODE') || die();

if (!\BeechIt\FalSecuredownload\Configuration\ExtensionConfiguration::trackDownloads()) {
    return;
}

$additionalColumns = [
    'downloads' => [
        'exclude' => true,
        'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:downloadStatistics.label',
        'config' => [
            'type' => 'input',
            'renderType' => 'falSecureDownloadStats'
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'downloads');
