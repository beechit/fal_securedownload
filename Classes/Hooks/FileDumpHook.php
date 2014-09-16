<?php
namespace BeechIt\FalSecuredownload\Hooks;

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

/**
 * FileDumpHook
 */
class FileDumpHook implements \TYPO3\CMS\Core\Resource\Hook\FileDumpEIDHookInterface {

	/**
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	protected $feUser;

	/**
	 * @var \TYPO3\CMS\Core\Resource\File
	 */
	protected $originalFile;

	/**
	 * @var string
	 */
	protected $redirectUrl;

	/**
	 * Constructor
	 */
	public function __construct() {
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['login_redirect_url'])) {
			$this->redirectUrl = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['login_redirect_url'];
		}
	}

	/**
	 * Perform custom security/access when accessing file
	 * Method should issue 403 if access is rejected
	 * or 401 if authentication is required
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceInterface $file
	 * @return void
	 */
	public function checkFileAccess(\TYPO3\CMS\Core\Resource\ResourceInterface $file) {

		if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
			$this->originalFile = $file->getOriginalFile();
		} else {
			$this->originalFile = $file;
		}

		if (!$this->checkPermissions()) {
			if (!$this->isLoggedIn() && $this->redirectUrl !== NULL) {
				$this->redirectToLogin();
			} else {
				$this->exitScript('No access!');
			}
		}

		// todo: find a nicer way to force the download. Other hooks are blocked by this
		if (isset($_REQUEST['download'])) {
			$file->getStorage()->dumpFileContents($file, TRUE);
			exit;
		}
	}

	/**
	 * Check if user is logged in
	 *
	 * @return bool
	 */
	protected function isLoggedIn() {
		$this->initializeUserAuthentication();
		return is_array($this->feUser->user) && $this->feUser->user['uid'] ? TRUE : FALSE;
	}

	/**
	 * Check if current user has enough permissions to view file
	 *
	 * @return bool
	 */
	protected function checkPermissions() {

		$this->initializeUserAuthentication();

		/** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
		$checkPermissionsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'BeechIt\\FalSecuredownload\\Security\\CheckPermissions');

		$userFeGroups = !$this->feUser->user ? FALSE : $this->feUser->groupData['uid'];

		return $checkPermissionsService->checkFileAccess($this->originalFile, $userFeGroups);
	}

	/**
	 * Initialise feUser
	 */
	protected function initializeUserAuthentication() {
		if ($this->feUser === NULL) {
			$this->feUser = \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser();
			$this->feUser->fetchGroupData();
		}
	}

	/**
	 * Exit with a error message
	 *
	 * @param string $message
	 */
	protected function exitScript($message) {
		header('HTTP/1.1 403 Forbidden');
		exit($message);
	}

	/**
	 * Redirect to login page
	 */
	protected function redirectToLogin() {
		$login_redirect_uri = str_replace(
			'###REQUEST_URI###',
			rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')),
			$this->redirectUrl
		);
		header('location: ' . $login_redirect_uri);
		exit;
	}
}