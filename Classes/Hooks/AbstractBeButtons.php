<?php

declare(strict_types=1);

/*
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
 */

namespace BeechIt\FalSecuredownload\Hooks;

use BeechIt\FalSecuredownload\Service\Utility;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract utility class for classes that want to add BE buttons to edit folder permissions
 */
abstract class AbstractBeButtons
{

    protected ResourceFactory $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Generate album add/edit buttons for click menu or toolbar
     *
     * @throws RouteNotFoundException
     */
    protected function generateButtons(string $combinedIdentifier): array
    {
        $buttons = [];

        if (!$GLOBALS['BE_USER']->user) {
            return $buttons;
        }

        // In some folder copy/move actions in file list an invalid id is passed
        try {
            $folder = $this->resourceFactory->retrieveFileOrFolderObject($combinedIdentifier);
        } catch (ResourceDoesNotExistException|InsufficientFolderAccessPermissionsException $exception) {
            $folder = null;
        }

        if ($folder instanceof Folder
            && !$folder->getStorage()->isPublic()
            && in_array(
                $folder->getRole(),
                [Folder::ROLE_DEFAULT, Folder::ROLE_USERUPLOAD]
            )
        ) {
            /** @var Utility $utility */
            $utility = GeneralUtility::makeInstance(Utility::class);
            $folderRecord = $utility->getFolderRecord($folder);

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

    protected function getIcon(string $name): Icon
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon('action-' . $name, Icon::SIZE_SMALL);
    }

    /**
     * Build edit url
     *
     * @param int $uid Media album uid
     * @return UriInterface
     * @throws RouteNotFoundException
     */
    protected function buildEditUrl(int $uid): UriInterface
    {
        return $this->buildUrl([
            'edit' => [
                'tx_falsecuredownload_folder' => [
                    $uid => 'edit'
                ]
            ]
        ]);
    }

    /**
     * Build Add new media album url
     *
     * @throws RouteNotFoundException
     */
    protected function buildAddUrl(Folder $folder): UriInterface
    {
        return $this->buildUrl([
            'edit' => [
                'tx_falsecuredownload_folder' => [
                    0 => 'new'
                ]
            ],
            'defVals' => [
                'tx_falsecuredownload_folder' => [
                    'storage' => $folder->getStorage()->getUid(),
                    'folder' => $folder->getIdentifier(),
                    'folder_hash' => $folder->getHashedIdentifier(),
                ]
            ]
        ]);
    }

    /**
     * Build record edit url
     *
     * @param array $parameters URL parameters
     * @return UriInterface
     * @throws RouteNotFoundException
     */
    protected function buildUrl(array $parameters): UriInterface
    {
        $parameters['returnUrl'] = GeneralUtility::getIndpEnv('REQUEST_URI');

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return $uriBuilder->buildUriFromRoute('record_edit', $parameters);
    }

    /**
     * Create link/button
     *
     * @param string $title
     * @param string $shortTitle
     * @param Icon $icon
     * @param UriInterface $url
     * @param bool $addReturnUrl
     * @return string|array
     */
    abstract protected function createLink(string $title, string $shortTitle, Icon $icon, UriInterface $url, bool $addReturnUrl = true);


    protected function getLangService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Get language string
     */
    protected function sL(string $key): string
    {
        return $this->getLangService()->sL('LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:' . $key);
    }
}
