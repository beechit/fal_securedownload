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
    ]
];

$GLOBALS['TCA']['sys_file_metadata'] = array_merge_recursive(
	$GLOBALS['TCA']['sys_file_metadata'],
	[
		'ctrl'=>[
			'enablecolumns' => [
				'starttime' => 'starttime',
				'endtime' => 'endtime',
			]
		],
		'columns' => [
			'starttime' => [
				'exclude' => true,
				'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
				'config' => [
						'type' => 'input',
						'renderType' => 'inputDateTime',
						'eval' => 'datetime,int',
						'default' => 0
				]
			],
			'endtime' => [
				'exclude' => true,
				'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
				'config' => [
						'type' => 'input',
						'renderType' => 'inputDateTime',
						'eval' => 'datetime,int',
						'default' => 0
				]
			]
		],
		'palettes' => [
				'timeRestriction' => ['showitem' => 'starttime, endtime']
		],
	]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'fe_groups');

// Add new Palette with Time Settings
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'sys_file_metadata',
		'--div--;LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_db.xlf:timeRestrictions,
			--palette--;;timeRestriction');
