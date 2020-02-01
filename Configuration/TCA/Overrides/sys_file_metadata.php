<?php
defined('TYPO3_MODE') or die();

$additionalColumns = [
    'fe_groups' => [
        'exclude' => true,
        'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'size' => 5,
            'maxitems' => 20,
            'items' => [
                [
                    'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                    -2
                ],
                [
                    'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                    '--div--'
                ]
            ],
            'exclusiveKeys' => '-1,-2',
            'foreign_table' => 'fe_groups',
            'foreign_table_where' => 'ORDER BY fe_groups.title',
            'enableMultiSelectFilterTextfield' => true,
        ]
    ],
    'download_name' => [
        'exclude' => true,
        'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:downloadName',
    		'description' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:downloadName.description',
        'config' => [
            'type' => 'input',
            'size' => '255',
            'max' => '255',
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'fe_groups');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'download_name','', 'after:alternative');