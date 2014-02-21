<?php
namespace BeechIt\FalSecuredownload\Xclass;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frans Saris <franssaris@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Xclass to extend the FileList so custom folder icons are shown
 */
class FileList extends \TYPO3\CMS\Filelist\FileList {

	/**
	 * This returns tablerows for the directories in the array $items['sorting'].
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder[] $folders Folders of \TYPO3\CMS\Core\Resource\Folder
	 * @return string HTML table rows.
	 * @todo Define visibility
	 */
	public function formatDirList(array $folders) {
		$out = '';
		foreach ($folders as $folderName => $folderObject) {
			$role = $folderObject->getRole();
			if ($role === FolderInterface::ROLE_PROCESSING) {
				// don't show processing-folder
				continue;
			}
			if ($role !== FolderInterface::ROLE_DEFAULT) {
				$displayName = '<strong>' . htmlspecialchars($folderName) . '</strong>';
			} else {
				$displayName = htmlspecialchars($folderName);
			}

			list($flag, $code) = $this->fwd_rwd_nav();
			$out .= $code;
			if ($flag) {
				$isLocked = $folderObject instanceof \TYPO3\CMS\Core\Resource\InaccessibleFolder;
				$isWritable = $folderObject->checkActionPermission('write');

				// Initialization
				$this->counter++;
				list($_, $icon, $path) = $this->dirData($folderObject);
				// The icon with link

				if (!$isLocked) {
					$theIcon = $this->getFolderSpriteIcon($folderObject, $icon, array('title' => $folderName));
					if (!$this->clickMenus) {
						$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($theIcon, $folderObject->getCombinedIdentifier());
					}
				} else {
					$theIcon = $this->getFolderSpriteIcon($folderObject, $icon, array('title' => $folderName), array('status-overlay-locked' => array()));
				}
				// Preparing and getting the data-array
				$theData = array();
				if ($isLocked) {
					foreach ($this->fieldArray as $field) {
						$theData[$field] = '';
					}
					$theData['file'] = $displayName;
				} else {
					foreach ($this->fieldArray as $field) {
						switch ($field) {
							case 'size':
								$numFiles = $folderObject->getFileCount();
								$theData[$field] = $numFiles . ' ' . $GLOBALS['LANG']->getLL(($numFiles === 1 ? 'file' : 'files'), TRUE);
								break;
							case 'rw':
								$theData[$field] = '<span class="typo3-red"><strong>' . $GLOBALS['LANG']->getLL('read', TRUE) . '</strong></span>' . (!$isWritable ? '' : '<span class="typo3-red"><strong>' . $GLOBALS['LANG']->getLL('write', TRUE) . '</strong></span>');
								break;
							case 'fileext':
								$theData[$field] = $GLOBALS['LANG']->getLL('folder', TRUE);
								break;
							case 'tstamp':
								// @todo: FAL: how to get the mtime info -- $theData[$field] = \TYPO3\CMS\Backend\Utility\BackendUtility::date($theFile['tstamp']);
								$theData[$field] = '-';
								break;
							case 'file':
								$theData[$field] = $this->linkWrapDir($displayName, $folderObject);
								break;
							case '_CLIPBOARD_':
								$temp = '';
								if ($this->bigControlPanel) {
									$temp .= $this->makeEdit($folderObject);
								}
								$temp .= $this->makeClip($folderObject);
								$theData[$field] = $temp;
								break;
							case '_REF_':
								$theData[$field] = $this->makeRef($folderObject);
								break;
							default:
								$theData[$field] = GeneralUtility::fixed_lgd_cs($theFile[$field], $this->fixedL);
						}
					}
				}
				$out .= $this->addelement(1, $theIcon, $theData);
			}
			$this->eCounter++;
			$this->dirCounter = $this->eCounter;
		}
		return $out;
	}

	/**
	 * @param FolderInterface $folder
	 * @param $icon_classes
	 * @param array $options
	 * @param array $overlays
	 * @return string
	 */
	protected function getFolderSpriteIcon(FolderInterface $folder, $icon_classes, array $options = array(), array $overlays = array()) {

		if (!$folder->getStorage()->isPublic()) {

			/** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
			$checkPermissionsService = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Security\\CheckPermissions');

			// check if there are access restrictions in the rootline of this folder
			if (!$checkPermissionsService->checkFolderRootLineAccess($folder, FALSE)) {
				$overlays['status-overlay-access-restricted'] = array();
			}
		}

		return IconUtility::getSpriteIcon($icon_classes, $options, $overlays);
	}
}