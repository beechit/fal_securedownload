<?php
namespace BeechIt\FalSecuredownload\Security;

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

/**
 * Utility functions to check permissions
 *
 * @package BeechIt\FalSecuredownload\Security
 */
class CheckPermissions implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \BeechIt\FalSecuredownload\Service\Utility
	 */
	protected $utilityService;

	public function __construct() {
		$this->utilityService = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Service\\Utility');
	}

	/**
	 * @param Folder $folder
	 * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
	 * @return bool
	 */
	public function checkFolderRootLineAccess(Folder $folder, $userFeGroups) {

		// loop trough the root line of an folder and check the permissions of every folder
		foreach ($this->getFolderRootLine($folder) as $folder) {

			// fetch folder permissions record
			$folderRecord = $this->utilityService->getFolderRecord($folder);

			// if record found check permissions
			if ($folderRecord) {
				if (!$this->matchFeGroupsWithFeUser($folderRecord['fe_groups'], $userFeGroups)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	/**
	 * Get permissions set on folder (no root line check)
	 *
	 * @param Folder $folder
	 * @return bool|string FALSE or comma separated list of fe_group uids
	 */
	public function getFolderPermissions(Folder $folder) {
		$permissions = FALSE;
		$folderRecord = $this->utilityService->getFolderRecord($folder);
		if ($folderRecord) {
			$permissions = $folderRecord['fe_groups'] ?: FALSE;
		}
		return $permissions;
	}

	/**
	 * Get all folders in root line of given folder
	 *
	 * @param Folder $folder
	 * @return Folder[]
	 */
	public function getFolderRootLine(Folder $folder) {
		$rootLine = array($folder);
		$parentFolder = $folder->getParentFolder();
		$count = 0;
		while ($parentFolder->getIdentifier() !== $folder->getIdentifier()) {
			$rootLine[] = $parentFolder;
			$count++;
			if ($count > 999) {
				break;
			}
			$folder = $parentFolder;
			$parentFolder = $parentFolder->getParentFolder();
		}
		return array_reverse($rootLine);
	}

	/**
	 * Check if given groups match with the groups of a user
	 *
	 * @param string $groups
	 * @param bool|array $userFeGroups FALSE = no login, array() is the groups of the user
	 * @return bool
	 */
	public function matchFeGroupsWithFeUser($groups, $userFeGroups) {

		// no groups specified everyone has access
		if ($groups === '') {
			return TRUE;
		}

		// no login then no access
		if ($userFeGroups === FALSE) {
			return FALSE;
		}

		// enabled for all loggedIn Users
		if (strpos($groups, '-2') !== FALSE) {
			return TRUE;
		}

		// user not member of any group then no access
		if (!is_array($userFeGroups)) {
			return FALSE;
		}

		foreach (explode(',', $groups) as $feGroupUid) {
			if (in_array(trim($feGroupUid), $userFeGroups)) {
				return TRUE;
			}
		}

		return FALSE;
	}
}