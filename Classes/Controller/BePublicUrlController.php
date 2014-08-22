<?php
namespace BeechIt\FalSecuredownload\Controller;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 22-08-2014 16:04
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Ajax controller for public url in BE
 */
class BePublicUrlController {

	/**
	 * Dump file content
	 *
	 * Copy from /sysext/core/Resources/PHP/FileDumpEID.php
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj
	 */
	public function dumpFile($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {

		$parameters = array('eID' => 'dumpFile');
		if (GeneralUtility::_GP('t')) {
			$parameters['t'] = GeneralUtility::_GP('t');
		}
		if (GeneralUtility::_GP('f')) {
			$parameters['f'] = (int)GeneralUtility::_GP('f');
		}
		if (GeneralUtility::_GP('p')) {
			$parameters['p'] = (int)GeneralUtility::_GP('p');
		}

		if (GeneralUtility::hmac(implode('|', $parameters), 'BeResourceStorageDumpFile') === GeneralUtility::_GP('token')) {
			if (isset($parameters['f'])) {
				$file = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileObject($parameters['f']);
				if ($file->isDeleted() || $file->isMissing()) {
					$file = NULL;
				}
				$orgFile = $file;
			} else {
				/** @var \TYPO3\CMS\Core\Resource\ProcessedFile $file */
				$file = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ProcessedFileRepository')->findByUid($parameters['p']);
				if ($file->isDeleted()) {
					$file = NULL;
				}
				$orgFile = $file->getOriginalFile();
			}

			// Check file read permissions
			if (!$orgFile->getStorage()->checkFileActionPermission('read', $orgFile)) {
				HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_403);
			}

			if ($file === NULL) {
				HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_404);
			}

			$file->getStorage()->dumpFileContents($file);
		} else {
			HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_403);
		}
	}
}