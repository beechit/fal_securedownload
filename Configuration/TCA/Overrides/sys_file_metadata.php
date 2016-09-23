<?php
defined('TYPO3_MODE') or die();

$additionalColumns = array(
    'fe_groups' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'size' => 5,
            'maxitems' => 20,
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
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'fe_groups');
