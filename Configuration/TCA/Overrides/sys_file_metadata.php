<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$additionalColumns = [
    'fe_groups' => [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'size' => 5,
            'maxitems' => 20,
            'items' => [
                [
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                    'value' => -2,
                ],
                [
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                    'value' => '--div--',
                ],
            ],
            'exclusiveKeys' => '-1,-2',
            'foreign_table' => 'fe_groups',
            'foreign_table_where' => 'ORDER BY fe_groups.title',
        ],
    ],
];

$typo3Version = new Typo3Version();
if ($typo3Version->getMajorVersion() === 11) {
    foreach ($additionalColumns['fe_groups']['config']['items'] as &$item) {
        $item = array_values($item);
    }
}

ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'fe_groups');
