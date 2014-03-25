<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'BeechIt.' . $_EXTKEY,
	'Filetree',
	array(
		'FileTree' => 'tree',
	),
	// non-cacheable actions
	array(
		'FileTree' => 'tree',
	)
);

// FE FileTree leaf open/close state dispatcher
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['FalSecuredownloadFileTreeState'] =
	'EXT:fal_securedownload/Resources/Public/Php/FileTreeState.php';

// Resource Icon hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FileDumpEID.php']['checkFileAccess']['FalSecuredownload'] =
	'BeechIt\\FalSecuredownload\\Hooks\\FileDumpHook';

// Resource Icon hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideResourceIcon']['FalSecuredownload'] =
	'BeechIt\\FalSecuredownload\\Hooks\\IconUtilityHook';

if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderMove,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderMove'
	);
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderMove,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderMove'
	);
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderDelete,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderDelete'
	);
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderDelete,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderDelete'
	);
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderRename,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderRename'
	);
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderRename,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderRename'
	);
}

