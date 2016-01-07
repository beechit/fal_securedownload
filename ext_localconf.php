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

// FileDumpEID hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FileDumpEID.php']['checkFileAccess']['FalSecuredownload'] =
	'BeechIt\\FalSecuredownload\\Hooks\\FileDumpHook';

// Resource Icon hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideResourceIcon']['FalSecuredownload'] =
	'BeechIt\\FalSecuredownload\\Hooks\\IconUtilityHook';

if (TYPO3_MODE === 'BE') {

	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

	// Public url rendering in BE context
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
		'BeechIt\\FalSecuredownload\\Aspects\\PublicUrlAspect',
		'generatePublicUrl'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
		'FalSecuredownload::publicUrl',
		'BeechIt\\FalSecuredownload\\Controller\\BePublicUrlController->dumpFile'
	);

	// Page module hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['falsecuredownload_filetree'][$_EXTKEY] =
		'BeechIt\\FalSecuredownload\\Hooks\\CmsLayout->getExtensionSummary';

	// Add FolderPermission button to docheader of filelist
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['docHeaderButtonsHook']['FalSecuredownload'] =
		'BeechIt\\FalSecuredownload\\Hooks\\DocHeaderButtonsHook->addFolderPermissionsButton';

	// refresh file tree after change in tx_falsecuredownload_folder record
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
		'BeechIt\\FalSecuredownload\\Hooks\\ProcessDatamapHook';
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
		'BeechIt\\FalSecuredownload\\Hooks\\ProcessDatamapHook';

	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderMove,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderMove'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderMove,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderMove'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderDelete,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderDelete'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderDelete,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderDelete'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFolderRename,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'preFolderRename'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderRename,
		'BeechIt\\FalSecuredownload\\Hooks\\FolderChangedSlot',
		'postFolderRename'
	);

	// ext:ke_search custom indexer hook
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyFileIndexEntryFromContentIndexer'][] = 'BeechIt\\FalSecuredownload\\Hooks\\KeSearchFilesHook';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyFileIndexEntry'][] = 'BeechIt\\FalSecuredownload\\Hooks\\KeSearchFilesHook';

	// ext:solrfal enrich metadata and generate correct public url slot
	$signalSlotDispatcher->connect(
		'TYPO3\\Solr\\Solrfal\\Indexing\\DocumentFactory',
		'fileMetaDataRetrieved',
		'BeechIt\\FalSecuredownload\\Aspects\\SolrFalAspect',
		'fileMetaDataRetrieved'
	);
}

