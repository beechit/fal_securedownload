<?php
namespace BeechIt\FalSecuredownload\ViewHelpers\Security;

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
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\File;

/**
 * Asset access ViewHelper
 *
 * @package BeechIt\FalSecuredownload\ViewHelpers\Security
 */
class AssetAccessViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if the current logged in FE user has access to the given asset
	 * otherwise renders <f:else> child.
	 *
	 * @param Folder $folder
	 * @param File $file
	 * @return bool|string
	 */
	public function render(Folder $folder, File $file = NULL) {

		/** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
		$checkPermissionsService = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Security\\CheckPermissions');
		$userFeGroups = $this->getFeUserGroups();
		$access = FALSE;

		// check folder access
		if ($checkPermissionsService->checkFolderRootLineAccess($folder, $userFeGroups)) {
			if ($file === NULL) {
				$access = TRUE;
			} else {
				$feGroups = $file->getProperty('fe_groups');
				if ($feGroups !== '') {
					$access = $checkPermissionsService->matchFeGroupsWithFeUser($feGroups, $userFeGroups);
				} else {
					$access = TRUE;
				}
			}
		}

		if ($access) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * Determines whether the currently logged in FE user belongs to the specified usergroup
	 *
	 * @param string $role The usergroup (either the usergroup uid or its title)
	 * @return boolean|array FALSE when not logged in or else $GLOBALS['TSFE']->fe_user->groupData['uid']
	 */
	protected function getFeUserGroups() {
		if (!isset($GLOBALS['TSFE']) || !$GLOBALS['TSFE']->loginUser) {
			return FALSE;
		}
		return $GLOBALS['TSFE']->fe_user->groupData['uid'];
	}
}