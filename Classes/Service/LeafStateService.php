<?php
namespace BeechIt\FalSecuredownload\Service;

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

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class LeafStateService
 */
class LeafStateService implements SingletonInterface
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Save new leave state in user session
     *
     * @param string $folder
     * @param bool $open
     */
    public function saveLeafStateForUser(FrontendUserAuthentication $user, $folder, $open)
    {

        // check if folder exists
        $folderObject = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($folder);

        if ($folderObject) {
            $folderState = $this->getFolderState($user);
            if ($open) {
                $folderState[$folder] = true;
            } else {
                unset($folderState[$folder]);
            }
            $this->saveFolderState($user, $folderState);
        }
    }

    /**
     * Get leaf state from user session
     *
     * @param string $folder
     * @return bool
     */
    public function getLeafStateForUser(FrontendUserAuthentication $user, $folder)
    {
        $folderStates = $this->getFolderState($user);
        return !empty($folderStates[$folder]);
    }

    /**
     * Get leaf states from user session
     *
     * @return array|mixed
     */
    protected function getFolderState(FrontendUserAuthentication $user)
    {
        $folderStates = $user->getKey((!empty($user->user['uid']) && $user->uc !== null) ? 'user' : 'ses', 'LeafStateService');
        if ($folderStates) {
            $folderStates = unserialize($folderStates);
        }
        if (!is_array($folderStates)) {
            $folderStates = [];
        }
        return $folderStates;
    }

    /**
     * Save leaf states in user session
     */
    protected function saveFolderState(FrontendUserAuthentication $user, array $folderState)
    {
        $user->setKey(!empty($user->user['uid']) ? 'user' : 'ses', 'LeafStateService', serialize($folderState));
        $user->storeSessionData();
    }
}
