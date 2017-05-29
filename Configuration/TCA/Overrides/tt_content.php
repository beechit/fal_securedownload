<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'fal_securedownload',
    'Filetree',
    'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:plugin.title'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['falsecuredownload_filetree'] = 'layout,recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['falsecuredownload_filetree'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'falsecuredownload_filetree',
    'FILE:EXT:fal_securedownload/Configuration/FlexForms/FileTree.xml'
);
