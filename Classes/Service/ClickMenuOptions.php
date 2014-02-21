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

use TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * Add ClickMenuOptions in file list
 */
class ClickMenuOptions {

	/**
	 * Add create sys_file_collection icon to filemenu
	 *
	 * @param \TYPO3\CMS\Backend\ClickMenu\ClickMenu $parentObject Back-reference to the calling object
	 * @param array $menuItems Current list of menu items
	 * @param string $combinedIdentifier The combined identifier
	 * @param integer $uid Id of the clicked on item
	 *
	 * @return array Modified list of menu items
	 */
	public function main(\TYPO3\CMS\Backend\ClickMenu\ClickMenu $parentObject, $menuItems, $combinedIdentifier, $uid) {

		if (!$parentObject->isDBmenu) {
			$combinedIdentifier = rawurldecode($combinedIdentifier);
			/** @var $fileObject \TYPO3\CMS\Core\Resource\Folder */
			$folderObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()
				->retrieveFileOrFolderObject($combinedIdentifier);

			if ($folderObject && $folderObject instanceof \TYPO3\CMS\Core\Resource\Folder
				&& !$folderObject->getStorage()->isPublic()
				&& in_array($folderObject->getRole(), array(\TYPO3\CMS\Core\Resource\Folder::ROLE_DEFAULT, \TYPO3\CMS\Core\Resource\Folder::ROLE_USERUPLOAD))) {

				/** @var \BeechIt\FalSecuredownload\Service\Utility $utility */
				$utility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Service\\Utility');
				$folderRecord = $utility->getFolderRecord($folderObject);

				$menuItems[] = 'spacer';

				if ($folderRecord) {
					$menuItems[] = $parentObject->linkItem(
						$GLOBALS['LANG']->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:clickmenu.folderpermissions'),
						$parentObject->excludeIcon(IconUtility::getSpriteIcon('extensions-fal_securedownload-folder')),
						$parentObject->urlRefForCM("alt_doc.php?edit[tx_falsecuredownload_folder][".$folderRecord['uid']."]=edit", 'returnUrl')
					);

				} else {
					$menuItems[] = $parentObject->linkItem(
						$GLOBALS['LANG']->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:clickmenu.folderpermissions'),
						$parentObject->excludeIcon(IconUtility::getSpriteIcon('extensions-fal_securedownload-folder')),
						$parentObject->urlRefForCM("alt_doc.php?edit[tx_falsecuredownload_folder][0]=new&defVals[tx_falsecuredownload_folder][folder_hash]=".$folderObject->getHashedIdentifier()."&defVals[tx_falsecuredownload_folder][storage]=".$folderObject->getStorage()->getUid()."&defVals[tx_falsecuredownload_folder][folder]=".$folderObject->getIdentifier()."", 'returnUrl')
					);
				}
			}
		}

		return $menuItems;
	}
}