<?php
namespace BeechIt\FalSecuredownload\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frans Saris <frans@beech.it>
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

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract utility class for classes that want to add album add/edit buttons
 * somewhere like a ClickMenuOptions class.
 */
abstract class AbstractBeButtons
{
    /**
     * Generate album add/edit buttons for click menu or toolbar
     *
     * @param string $combinedIdentifier
     * @return array
     */
    protected function generateButtons($combinedIdentifier)
    {
        $buttons = array();

        // In some folder copy/move actions in file list a invalid id is passed
        try {
            /** @var $file \TYPO3\CMS\Core\Resource\Folder */
            $folder = ResourceFactory::getInstance()
                ->retrieveFileOrFolderObject($combinedIdentifier);
        } catch (ResourceDoesNotExistException $exception) {
            $folder = null;
        } catch (InsufficientFolderAccessPermissionsException $exception) {
            $folder = null;
        }

        if ($folder && $folder instanceof Folder
            && !$folder->getStorage()->isPublic()
            && in_array(
                $folder->getRole(),
                array(Folder::ROLE_DEFAULT, Folder::ROLE_USERUPLOAD)
            )
        ) {
            /** @var \BeechIt\FalSecuredownload\Service\Utility $utility */
            $utility = GeneralUtility::makeInstance('BeechIt\\FalSecuredownload\\Service\\Utility');
            $folderRecord = $utility->getFolderRecord($folder);

            $menuItems[] = 'spacer';

            if ($folderRecord) {
                $buttons[] = $this->createLink(
                    $this->sL('clickmenu.folderpermissions'),
                    $this->sL('clickmenu.folderpermissions'),
                    $this->getIcon('folder'),
                    $this->buildEditUrl($folderRecord['uid'])
                );

            } else {
                $buttons[] = $this->createLink(
                    $this->sL('clickmenu.folderpermissions'),
                    $this->sL('clickmenu.folderpermissions'),
                    $this->getIcon('folder'),
                    $this->buildAddUrl($folder)
                );
            }
        }
        return $buttons;
    }

    /**
     * @param string $name
     * @return string|Icon
     */
    protected function getIcon($name)
    {
        if (!GeneralUtility::compat_version('7.4')) {
            $icon = IconUtility::getSpriteIcon('extensions-fal_securedownload-' . $name);
        } else {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $icon = $iconFactory->getIcon('action-' . $name, Icon::SIZE_SMALL);
        }

        return $icon;
    }

    /**
     * Build edit url
     *
     * @param int $uid Media album uid
     * @return string
     */
    protected function buildEditUrl($uid)
    {
        if (!GeneralUtility::compat_version('7.4')) {
            return 'alt_doc.php?edit[tx_falsecuredownload_folder][' . $uid . ']=edit';
        } else {
            return BackendUtility::getModuleUrl('record_edit', array(
                'edit' => array(
                    'tx_falsecuredownload_folder' => array(
                        $uid => 'edit'
                    )
                ),
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
            ));
        }
    }

    /**
     * Build Add new media album url
     *
     * @param Folder $folder
     * @return string
     */
    protected function buildAddUrl(Folder $folder)
    {
        if (!GeneralUtility::compat_version('7.4')) {
            return 'alt_doc.php?edit[tx_falsecuredownload_folder][0]=new&' .
                   'defVals[tx_falsecuredownload_folder][folder_hash]=' . $folder->getHashedIdentifier() .
                   '&defVals[tx_falsecuredownload_folder][storage]=' . $folder->getStorage()->getUid() .
                   '&defVals[tx_falsecuredownload_folder][folder]=' . $folder->getIdentifier();
        } else {
            return BackendUtility::getModuleUrl('record_edit', array(
                'edit' => array(
                    'tx_falsecuredownload_folder' => array(
                        0 => 'new'
                    )
                ),
                'defVals' => array(
                    'tx_falsecuredownload_folder' => array(
                        'storage' => $folder->getStorage()->getUid(),
                        'folder' => $folder->getIdentifier(),
                        'folder_hash' => $folder->getHashedIdentifier(),
                    )
                ),
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
            ));
        }
    }

    /**
     * Create link/button
     *
     * @param string $title
     * @param string $shortTitle
     * @param string $icon
     * @param string $url
     * @param bool $addReturnUrl
     * @return string
     */
    abstract protected function createLink($title, $shortTitle, $icon, $url, $addReturnUrl = true);

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLangService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Get language string
     *
     * @param string $key
     * @param string $languageFile
     * @return string
     */
    protected function sL(
        $key,
        $languageFile = 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf'
    ) {
        return $this->getLangService()->sL($languageFile . ':' . $key);
    }
}
