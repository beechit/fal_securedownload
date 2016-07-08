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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FileDumpHook
 */
class FileDumpHook implements \TYPO3\CMS\Core\Resource\Hook\FileDumpEIDHookInterface
{

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $feUser;

    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $originalFile;

    /**
     * @var string
     */
    protected $loginRedirectUrl;

    /**
     * @var string
     */
    protected $noAccessRedirectUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['login_redirect_url'])) {
            $this->loginRedirectUrl = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['login_redirect_url'];
        }
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['no_access_redirect_url'])) {
            $this->noAccessRedirectUrl = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['no_access_redirect_url'];
        }
    }

    /**
     * Get feUser
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * Perform custom security/access when accessing file
     * Method should issue 403 if access is rejected
     * or 401 if authentication is required
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface $file
     * @return void
     */
    public function checkFileAccess(\TYPO3\CMS\Core\Resource\ResourceInterface $file)
    {

        if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
            $this->originalFile = $file->getOriginalFile();
        } else {
            $this->originalFile = $file;
        }

        if (!$this->checkPermissions()) {
            if (!$this->isLoggedIn()) {
                if ($this->loginRedirectUrl !== null) {
                    $this->redirectToUrl($this->loginRedirectUrl);
                } else {
                    $this->exitScript('Authentication required!', 401);
                }
            } else {
                if ($this->noAccessRedirectUrl !== null) {
                    $this->redirectToUrl($this->noAccessRedirectUrl);
                } else {
                    $this->exitScript('No access!', 403);
                }
            }
        }

        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
        $signalSlotDispatcher->dispatch(__CLASS__, 'BeforeFileDump', array($file, $this));

        // todo: find a nicer way to force the download. Other hooks are blocked by this
        if (isset($_REQUEST['download'])) {
            $file->getStorage()->dumpFileContents($file, true);
            exit;
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        $this->initializeUserAuthentication();
        return is_array($this->feUser->user) && $this->feUser->user['uid'] ? true : false;
    }

    /**
     * Check if current user has enough permissions to view file
     *
     * @return bool
     */
    protected function checkPermissions()
    {

        $this->initializeUserAuthentication();

        /** @var $checkPermissionsService \BeechIt\FalSecuredownload\Security\CheckPermissions */
        $checkPermissionsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'BeechIt\\FalSecuredownload\\Security\\CheckPermissions'
        );

        $userFeGroups = !$this->feUser->user ? false : $this->feUser->groupData['uid'];

        return $checkPermissionsService->checkFileAccess($this->originalFile, $userFeGroups);
    }

    /**
     * Initialise feUser
     */
    protected function initializeUserAuthentication()
    {
        if ($this->feUser === null) {
            $this->feUser = \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser();
            $this->feUser->fetchGroupData();
        }
    }

    /**
     * Exit with a error message
     *
     * @param string $message
     * @param int $httpCode
     */
    protected function exitScript($message, $httpCode = 403)
    {
        header('HTTP/1.1 ' . (int)$httpCode . ' Forbidden');
        exit($message);
    }

    /**
     * Redirect to url
     */
    protected function redirectToUrl($url)
    {
        $redirect_uri = str_replace(
            '###REQUEST_URI###',
            rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')),
            $url
        );
        header('location: ' . $redirect_uri);
        exit;
    }
}
