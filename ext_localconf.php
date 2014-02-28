<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// add TypoScript for the asset serving
// todo: remove when https://review.typo3.org/#/c/27760/ is in and create a hook for eID dumpFile
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('fal_securedownload', 'setup',
	'
	FalSecuredownload = PAGE
	FalSecuredownload {
		typeNum = 1337
		config {
			disableAllHeaderCode = 1
			admPanel = 0
		}
		10 = USER
		10.userFunc = BeechIt\FalSecuredownload\Resource\FileDelivery->deliver
	}
');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'BeechIt.' . $_EXTKEY,
	'Filetree',
	array(
		'FileTree' => 'tree',
	),
	// non-cacheable actions
	array(
	)
);

// FileTree leaf open/close state dispatcher
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['FalSecuredownloadFileTreeState'] =
	'EXT:fal_securedownload/Resources/Public/Php/FileTreeState.php';

// todo: remove when https://review.typo3.org/#/c/27760/ is in and create a hook for eID dumpFile
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
	'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
	\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
	'BeechIt\\FalSecuredownload\\Security\\PublicUrlAspect',
	'generatePublicUrl'
);

// extend the FileList FolderTree to change the icons
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Filelist\\FileListFolderTree'] = array(
	'className' => 'BeechIt\\FalSecuredownload\\Xclass\\FileListFolderTree'
);
// extend the FolderTreeView to change the icons
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['\TYPO3\\CMS\\Backend\\Tree\\View\\FolderTreeView'] = array(
	'className' => 'BeechIt\\FalSecuredownload\\Xclass\\FolderTreeView'
);
// extend the FileList to change the icons
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Filelist\\FileList'] = array(
	'className' => 'BeechIt\\FalSecuredownload\\Xclass\\FileList'
);
// extend the FileList to change the icons
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TBE_FolderTree'] = array(
	'className' => 'BeechIt\\FalSecuredownload\\Xclass\\TBE_FolderTree'
);

// Resource Icon hook
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideResourceIcon']['FalSecuredownload'] =
//	'BeechIt\\FalSecuredownload\\Hooks\\IconUtility';

