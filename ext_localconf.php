<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'FalSecuredownload',
    'Filetree',
    [
        BeechIt\FalSecuredownload\Controller\FileTreeController::class => 'tree',
    ],
    // non-cacheable actions
    [
        BeechIt\FalSecuredownload\Controller\FileTreeController::class => 'tree',
    ]
);

// FE FileTree leaf open/close state dispatcher
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['FalSecuredownloadFileTreeState'] =
    \BeechIt\FalSecuredownload\Controller\FileTreeStateController::class . '::saveLeafState';

// FileDumpEID hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FileDumpEID.php']['checkFileAccess']['FalSecuredownload'] =
    \BeechIt\FalSecuredownload\Hooks\FileDumpHook::class;

if (TYPO3_MODE === 'BE') {

    // Page module hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['falsecuredownload_filetree']['fal_securedownload'] =
        \BeechIt\FalSecuredownload\Hooks\CmsLayout::class . '->getExtensionSummary';

    // Add FolderPermission button to docheader of filelist
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['FalSecuredownload'] =
        \BeechIt\FalSecuredownload\Hooks\DocHeaderButtonsHook::class . '->getButtons';

    // Context menu
    $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][1547242135]
        = \BeechIt\FalSecuredownload\ContextMenu\ItemProvider::class;

    // refresh file tree after change in tx_falsecuredownload_folder record
    $GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        \BeechIt\FalSecuredownload\Hooks\ProcessDatamapHook::class;
    $GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
        \BeechIt\FalSecuredownload\Hooks\ProcessDatamapHook::class;

    // ext:ke_search custom indexer hook
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyFileIndexEntryFromContentIndexer'][] = \BeechIt\FalSecuredownload\Hooks\KeSearchFilesHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyFileIndexEntry'][] = \BeechIt\FalSecuredownload\Hooks\KeSearchFilesHook::class;

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('solrfal')) {
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

        // @Todo convert this to event listener
        // ext:solrfal enrich metadata and generate correct public url slot
        $signalSlotDispatcher->connect(
            \ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory::class,
            'fileMetaDataRetrieved',
            \BeechIt\FalSecuredownload\Aspects\SolrFalAspect::class,
            'fileMetaDataRetrieved'
        );
    }

    if (\BeechIt\FalSecuredownload\Configuration\ExtensionConfiguration::trackDownloads()) {
        // register FormEngine node for rendering download statistics in fe_users
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1470920616] = [
            'nodeName' => 'falSecureDownloadStats',
            'priority' => 40,
            'class' => \BeechIt\FalSecuredownload\FormEngine\DownloadStatistics::class,
        ];
    }
}
