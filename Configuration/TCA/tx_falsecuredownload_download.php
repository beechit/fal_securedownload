<?php

use TYPO3\CMS\Core\Information\Typo3Version;

defined('TYPO3') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:tx_falsecuredownload_download',
        'label' => 'file',
        'crdate' => 'crdate',
        'tstamp' => 'tstamp',
        'hideTable' => true,
        'rootLevel' => true,
        'default_sortby' => 'ORDER BY file ASC',
        'security' => [
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ],
        'iconfile' => 'EXT:fal_securedownload/Resources/Public/Icons/download.png',
    ],
    'types' => [
        '0' => ['showitem' => '--palette--;;filePalette'],
    ],
    'palettes' => [
        // File palette, hidden but needs to be included all the time
        'filePalette' => [
            'showitem' => 'feuser,file',
            'isHiddenPalette' => true,
        ],
    ],
    'columns' => [
        'file' => [
            'exclude' => false,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:file',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
                'allowed' => 'sys_file',
            ],
        ],
        'feuser' => [
            'exclude' => false,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:fe_user',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
                'allowed' => 'fe_users',
            ],
        ],
    ],
];

return $tca;
