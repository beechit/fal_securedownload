<?php
defined('TYPO3_MODE') or die();

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:tx_falsecuredownload_folder',
        'label' => 'folder',
        'tstamp' => 'tstamp',
        'hideTable' => true,
        'rootLevel' => true,
        'default_sortby' => 'ORDER BY folder ASC',
        'dividers2tabs' => false,
        'security' => array(
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ),
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('fal_securedownload') . 'Resources/Public/Icons/folder.png'
    ),
    'interface' => array(
        'showRecordFieldList' => 'fe_groups, storage, folder, folder_hash'
    ),
    'types' => array(
        '0' => array('showitem' => 'fe_groups,--palette--;;filePalette'),
    ),
    'palettes' => array(
        // File palette, hidden but needs to be included all the time
        'filePalette' => array(
            'showitem' => 'storage, folder, folder_hash',
            'isHiddenPalette' => true
        )
    ),
    'columns' => array(
        'storage' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:storage',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
                'allowed' => 'sys_file_storage'
            )
        ),
        'folder' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:folder',
            'config' => array(
                'type' => 'input',
                'size' => '30'
            )
        ),
        'folder_hash' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:folder',
            'config' => array(
                'type' => 'input',
                'size' => '30'
            )
        ),
        'fe_groups' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 20,
                'maxitems' => 40,
                'items' => array(
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                        -2
                    ),
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    )
                ),
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            )
        )
    )
);
