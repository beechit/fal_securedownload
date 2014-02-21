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
 * Xclass to extend the FileListFolderTree so custom icons are shown
 */
class FileListFolderTree extends \TYPO3\CMS\Backend\Tree\View\FolderTreeView {

	/**
	 * Get a tree for one storage
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storageObject
	 * @return void
	 */
	public function getBrowseableTreeForStorage(\TYPO3\CMS\Core\Resource\ResourceStorage $storageObject) {
		// If there are filemounts, show each, otherwise just the rootlevel folder
		$fileMounts = $storageObject->getFileMounts();
		$rootLevelFolders = array();
		if (count($fileMounts)) {
			foreach ($fileMounts as $fileMountInfo) {
				$rootLevelFolders[] = array(
					'folder' => $fileMountInfo['folder'],
					'name' => $fileMountInfo['title']
				);
			}
		} else {
			$rootLevelFolders[] = array(
				'folder' => $storageObject->getRootLevelFolder(),
				'name' => $storageObject->getName()
			);
		}
		// Clean the tree
		$this->reset();
		// Go through all "root level folders" of this tree (can be the rootlevel folder or any file mount points)
		foreach ($rootLevelFolders as $rootLevelFolderInfo) {
			/** @var $rootLevelFolder \TYPO3\CMS\Core\Resource\Folder */
			$rootLevelFolder = $rootLevelFolderInfo['folder'];
			$rootLevelFolderName = $rootLevelFolderInfo['name'];
			$folderHashSpecUID = GeneralUtility::md5int($rootLevelFolder->getCombinedIdentifier());
			$this->specUIDmap[$folderHashSpecUID] = $rootLevelFolder->getCombinedIdentifier();
			// Hash key
			$storageHashNumber = $this->getShortHashNumberForStorage($storageObject, $rootLevelFolder);
			// Set first:
			$this->bank = $storageHashNumber;
			$isOpen = $this->stored[$storageHashNumber][$folderHashSpecUID] || $this->expandFirst;
			// Set PM icon:
			$cmd = $this->generateExpandCollapseParameter($this->bank, !$isOpen, $rootLevelFolder);
			if (!$storageObject->isBrowsable() || $this->getNumberOfSubfolders($rootLevelFolder) === 0) {
				$rootIcon = 'blank';
			} elseif (!$isOpen) {
				$rootIcon = 'plusonly';
			} else {
				$rootIcon = 'minusonly';
			}
			$icon = '<img' . IconUtility::skinImg($this->backPath, ('gfx/ol/' . $rootIcon . '.gif')) . ' alt="" />';
			// Only link icon if storage is browseable
			if (in_array($rootIcon, array('minusonly', 'plusonly'))) {
				$firstHtml = $this->PM_ATagWrap($icon, $cmd);
			} else {
				$firstHtml = $icon;
			}
			// @todo: create sprite icons for user/group mounts etc
			if ($storageObject->isBrowsable() === FALSE) {
				$icon = 'apps-filetree-folder-locked';
			} else {
				$icon = 'apps-filetree-root';
			}
			// Mark a storage which is not online, as offline
			// maybe someday there will be a special icon for this
			if ($storageObject->isOnline() === FALSE) {
				$rootLevelFolderName .= ' (' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_file.xlf:sys_file_storage.isOffline') . ')';
			}
			// Preparing rootRec for the mount
			$firstHtml .= $this->wrapIcon($this->getFolderSpriteIcon($rootLevelFolder, $icon), $rootLevelFolder);
			$row = array(
				'uid' => $folderHashSpecUID,
				'title' => $rootLevelFolderName,
				'path' => $rootLevelFolder->getCombinedIdentifier(),
				'folder' => $rootLevelFolder
			);
			// Add the storage root to ->tree
			$this->tree[] = array(
				'HTML' => $firstHtml,
				'row' => $row,
				'bank' => $this->bank,
				// hasSub is TRUE when the root of the storage is expanded
				'hasSub' => $isOpen && $storageObject->isBrowsable()
			);
			// If the mount is expanded, go down:
			if ($isOpen && $storageObject->isBrowsable()) {
				// Set depth:
				$this->getFolderTree($rootLevelFolder, 999);
			}
		}
	}


	/**
	 * Fetches the data for the tree
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folderObject the folderobject
	 * @param integer $depth Max depth (recursivity limit)
	 * @param string $type HTML-code prefix for recursive calls.
	 * @return integer The count of items on the level
	 * @see getBrowsableTree()
	 */
	public function getFolderTree(\TYPO3\CMS\Core\Resource\Folder $folderObject, $depth = 999, $type = '') {
		$depth = (int)$depth;

		// This generates the directory tree
		/* array of \TYPO3\CMS\Core\Resource\Folder */
		if ($folderObject instanceof \TYPO3\CMS\Core\Resource\InaccessibleFolder) {
			$subFolders = array();
		} else {
			$subFolders = $folderObject->getSubfolders();
			$subFolders = \TYPO3\CMS\Core\Resource\Utility\ListUtility::resolveSpecialFolderNames($subFolders);
			uksort($subFolders, 'strnatcasecmp');
		}

		$totalSubFolders = count($subFolders);
		$HTML = '';
		$subFolderCounter = 0;
		foreach ($subFolders as $subFolderName => $subFolder) {
			$subFolderCounter++;
			// Reserve space.
			$this->tree[] = array();
			// Get the key for this space
			end($this->tree);
			$isLocked = $subFolder instanceof \TYPO3\CMS\Core\Resource\InaccessibleFolder;
			$treeKey = key($this->tree);
			$specUID = GeneralUtility::md5int($subFolder->getCombinedIdentifier());
			$this->specUIDmap[$specUID] = $subFolder->getCombinedIdentifier();
			$row = array(
				'uid' => $specUID,
				'path' => $subFolder->getCombinedIdentifier(),
				'title' => $subFolderName,
				'folder' => $subFolder
			);
			// Make a recursive call to the next level
			if (!$isLocked && $depth > 1 && $this->expandNext($specUID)) {
				$nextCount = $this->getFolderTree($subFolder, $depth - 1, $type);
				// Set "did expand" flag
				$isOpen = 1;
			} else {
				$nextCount = $isLocked ? 0 : $this->getNumberOfSubfolders($subFolder);
				// Clear "did expand" flag
				$isOpen = 0;
			}
			// Set HTML-icons, if any:
			if ($this->makeHTML) {
				$HTML = $this->PMicon($subFolder, $subFolderCounter, $totalSubFolders, $nextCount, $isOpen);
				$type = '';
				$overlays = array();

				if ($isLocked) {
					$type = 'readonly';
					$overlays = array('status-overlay-locked' => array());
				}
				if ($isOpen) {
					$icon = 'apps-filetree-folder-opened';
				} else {
					$icon = 'apps-filetree-folder-default';
				}
				$role = $subFolder->getRole();
				if ($role !== FolderInterface::ROLE_DEFAULT) {
					$row['_title'] = '<strong>' . $subFolderName . '</strong>';
				}
				if ($role === FolderInterface::ROLE_TEMPORARY) {
					$icon = 'apps-filetree-folder-temp';
				} elseif ($role === FolderInterface::ROLE_RECYCLER) {
					$icon = 'apps-filetree-folder-recycler';
				}
				$icon = $this->getFolderSpriteIcon($subFolder, $icon, array('title' => $subFolderName), $overlays);
				$HTML .= $this->wrapIcon($icon, $subFolder);
			}
			// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = array(
				'row' => $row,
				'HTML' => $HTML,
				'hasSub' => $nextCount && $this->expandNext($specUID),
				'isFirst' => $subFolderCounter == 1,
				'isLast' => FALSE,
				'invertedDepth' => $depth,
				'bank' => $this->bank
			);
		}
		if ($subFolderCounter > 0) {
			$this->tree[$treeKey]['isLast'] = TRUE;
		}
		return $totalSubFolders;
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