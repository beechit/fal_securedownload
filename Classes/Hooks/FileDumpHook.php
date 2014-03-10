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
	 * Perform custom security/access when accessing file
	 * Method should issue 403 if access is rejected
	 * or 401 if authentication is required
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceInterface $file
	 * @return void
	 */
	public function checkFileAccess(\TYPO3\CMS\Core\Resource\ResourceInterface $file) {

		// if BE login no access check
		// Todo: in eID context BE_USER isn't available determine of this is needed/required.
		if (!empty($GLOBALS['BE_USER'])) {
			return;
		}

		if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
			$this->originalFile = $file->getOriginalFile();
		} else {
			$this->originalFile = $file;
		}

		if (!$this->checkPermissions()) {
			$this->exitScript('No access!');
		}
	}

	/**
	 * @return bool
	 */
	protected function checkPermissions() {

		$this->initializeUserAuthentication();

		/** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
		$checkPermissionsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Security\\CheckPermissions');

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
	 * Initialise feUser
	 */
	protected function initializeUserAuthentication() {
		$this->feUser = \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser();
		$this->feUser->fetchGroupData();
	}

	/**
	 * @param string $message
	 */
	protected function exitScript($message) {
		header('HTTP/1.1 403 Forbidden');
		exit($message);
	}

}