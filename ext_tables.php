<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Filetree',
    'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:plugin.title'
);

$pluginSignature = str_replace('_', '', $_EXTKEY) . '_filetree';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,recursive,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/FileTree.xml'
);



if (TYPO3_MODE === 'BE') {
    // Add click menu item:
    $GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = [
        'name' => 'BeechIt\\FalSecuredownload\\Hooks\\ClickMenuOptions'
    ];
}


// Initiate
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconRegistry');
$iconRegistry->registerIcon(
    'action-folder',
    'TYPO3\\CMS\\Core\\Imaging\\IconProvider\\SvgIconProvider',
    [
        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/folder.svg',
    ]
);
$iconRegistry->registerIcon(
    'overlay-inherited-permissions',
    'TYPO3\\CMS\\Core\\Imaging\\IconProvider\\SvgIconProvider',
    [
        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/overlay-inherited-permissions.svg',
    ]
);
