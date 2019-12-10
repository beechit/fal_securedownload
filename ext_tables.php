<?php
defined('TYPO3_MODE') or die();

// Initiate
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'action-folder',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    [
        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/folder.svg',
    ]
);
$iconRegistry->registerIcon(
    'overlay-inherited-permissions',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    [
        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/overlay-inherited-permissions.svg',
    ]
);
