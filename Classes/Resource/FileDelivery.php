<?php
namespace BeechIt\FalSecuredownload\Resource;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <frans@beech.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;

/**
 * FileDeliverys
 *
 * @package BeechIt\FalSecuredownload\Resource
 */
class FileDelivery {

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	protected $feUser;

	/**
	 * @var string
	 */
	protected $hash;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var \TYPO3\CMS\Core\Resource\AbstractFile
	 */
	protected $file;

	/**
	 * @var \TYPO3\CMS\Core\Resource\File
	 */
	protected $originalFile;

	/**
	 * Check the access rights
	 */
	function __construct() {

		//
		$this->hash = GeneralUtility::_GP('h');
		$this->identifier = GeneralUtility::_GP('file');

		if (!$this->hashValid()) {
			$this->exitScript('Hash invalid! Access denied!');
		}

		list($type, $fileUid) = explode(':', $this->identifier);
		switch ($type) {

			// ProcessedFile
			case 'p':
				/** @var $processedFileRepository \BeechIt\FalSecuredownload\Domain\Repository\ProcessedFileRepository */
				$processedFileRepository = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Domain\\Repository\\ProcessedFileRepository');
				try {
					$this->file = $processedFileRepository->findByUid($fileUid);
					$this->originalFile = $this->file->getOriginalFile();
				} catch (\Exception $exeption) {}
				break;

			// File
			case 'f':
				$resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\resourceFactory');
				$this->file = $resourceFactory->getFileObject($fileUid);
				$this->originalFile = $this->file;
				break;
			default:
				$this->exitScript('Unknown request!');
		}


		if (!$this->file) {
			$this->exitScript('File not found!');
		}

		// if BE login no access check
		if (empty($GLOBALS['BE_USER'])) {
			$this->initializeUserAuthentication();
			if (!$this->checkFileAccess()) {
				$this->exitScript('No access!');
			}
		}
	}

	/**
	 * Initialise feUser
	 */
	protected function initializeUserAuthentication() {
		$this->feUser = $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * @return boolean
	 */
	protected function hashValid() {
		return (GeneralUtility::hmac($this->identifier, 'fal_securedownload') === $this->hash);
	}

	/**
	 * @param string $message
	 */
	protected function exitScript($message) {
		header('HTTP/1.1 403 Forbidden');
		exit($message);
	}

	/**
	 * Check file access rights
	 * @return bool
	 */
	protected function checkFileAccess() {

		/** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
		$checkPermissionsService = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Security\\CheckPermissions');

		$userFeGroups = !$this->feUser->user ? FALSE : $this->feUser->groupData['uid'];

		// check folder access
		if ($checkPermissionsService->checkFolderRootLineAccess($this->originalFile->getParentFolder(), $userFeGroups)) {
			$feGroups = $this->originalFile->getProperty('fe_groups');
			if ($feGroups !== '') {
				return $checkPermissionsService->matchFeGroupsWithFeUser($feGroups, $userFeGroups);
			}
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Deliver file
	 */
	public function deliver() {

		// todo: remove 'if' when https://review.typo3.org/#/c/27760/ is in
		if ($this->file instanceof ProcessedFile) {

			$fileInfo = $this->file->getStorage()->getFileInfoByIdentifier($this->file->getIdentifier(), array('mimetype', 'size', 'mtime'));
			header('Content-Type: ' . $fileInfo['mimetype']);
			header('Content-Length: ' . $fileInfo['size']);

			// Cache-Control header is needed here to solve an issue with browser IE8 and lower
			// See for more information: http://support.microsoft.com/kb/323308
			header("Cache-Control: ''");
			header('Last-Modified: ' .
				gmdate('D, d M Y H:i:s', $fileInfo['mtime']) . ' GMT',
				TRUE,
				200
			);
			ob_clean();
			echo $this->file->getContents();
			flush();
			exit();

		} else {
			$this->file->getStorage()->dumpFileContents($this->file);
		}
	}
}