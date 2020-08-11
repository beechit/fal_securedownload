<?php

$folder = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('folder');
if (!empty($folder)) {
    $open = (bool)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('open');

    /** @var $leafStateService \BeechIt\FalSecuredownload\Service\LeafStateService */
    $leafStateService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Service\\LeafStateService');
    $leafStateService->saveLeafStateForUser($GLOBALS['TSFE']->fe_user, $folder, $open);
}
