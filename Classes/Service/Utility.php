<?php
namespace BeechIt\FalSecuredownload\Service;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\Folder;

class Utility implements \TYPO3\CMS\Core\SingletonInterface {

	static protected $folderRecordCache = array();

	/**
	 * Get folder configuration record
	 *
	 * @return array
	 */
	public function getFolderRecord(Folder $folder) {

		if (!isset(self::$folderRecordCache[$folder->getCombinedIdentifier()])
			|| !array_key_exists($folder->getCombinedIdentifier(), self::$folderRecordCache)
		) {
			$record = $this->getDatabase()->exec_SELECTgetSingleRow(
				'*',
				'tx_falsecuredownload_folder',
				'storage = '.(int)$folder->getStorage()->getUid().'
				AND folder_hash = "'.$folder->getHashedIdentifier().'"'
			);
			// cache results
			self::$folderRecordCache[$folder->getCombinedIdentifier()] = $record;
		}

		return self::$folderRecordCache[$folder->getCombinedIdentifier()];
	}

	/**
	 * Gets the database object.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBeUser() {
		return $GLOBALS['BE_USER'];
	}

}
