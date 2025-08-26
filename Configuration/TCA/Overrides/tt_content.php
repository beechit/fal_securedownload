<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

ExtensionUtility::registerPlugin(
    'fal_securedownload',
    'Filetree',
    'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:plugin.title'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'falsecuredownload_filetree', 'after:subheader');
ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:fal_securedownload/Configuration/FlexForms/FileTree.xml',
    'falsecuredownload_filetree'
);
