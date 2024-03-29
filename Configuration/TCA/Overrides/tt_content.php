<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

ExtensionUtility::registerPlugin(
    'fal_securedownload',
    'Filetree',
    'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:plugin.title'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['falsecuredownload_filetree'] = 'layout,recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['falsecuredownload_filetree'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'falsecuredownload_filetree',
    'FILE:EXT:fal_securedownload/Configuration/FlexForms/FileTree.xml'
);
