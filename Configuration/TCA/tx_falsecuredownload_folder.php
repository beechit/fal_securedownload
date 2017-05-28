<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:tx_falsecuredownload_folder',
        'label' => 'folder',
        'tstamp' => 'tstamp',
        'hideTable' => true,
        'rootLevel' => true,
        'default_sortby' => 'ORDER BY folder ASC',
        'security' => [
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ],
        'iconfile' => 'EXT:fal_securedownload/Resources/Public/Icons/folder.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'fe_groups, storage, folder, folder_hash'
    ],
    'types' => [
        '0' => ['showitem' => 'fe_groups,--palette--;;filePalette'],
    ],
    'palettes' => [
        // File palette, hidden but needs to be included all the time
        'filePalette' => [
            'showitem' => 'storage, folder, folder_hash',
            'isHiddenPalette' => true
        ]
    ],
    'columns' => [
        'storage' => [
            'exclude' => false,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:storage',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
                'allowed' => 'sys_file_storage'
            ]
        ],
        'folder' => [
            'exclude' => false,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:folder',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'folder_hash' => [
            'exclude' => false,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:folder',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'fe_groups' => [
            'exclude' => false,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 20,
                'maxitems' => 40,
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                        -2
                    ],
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    ]
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            ]
        ]
    ]
];
