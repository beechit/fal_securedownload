<?php
defined('TYPO3_MODE') or die();

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
