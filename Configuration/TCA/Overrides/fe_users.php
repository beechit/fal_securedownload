<?php
defined('TYPO3_MODE') || die();

if (!\BeechIt\FalSecuredownload\Configuration\ExtensionConfiguration::trackDownloads()) {
    return;
}

$additionalColumns = array(
    'downloads' => array(
        'exclude' => 1,
        'config' => array(
            'type' => 'input',
            'renderType' => 'falSecureDownloadStats'
        )
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'downloads');
