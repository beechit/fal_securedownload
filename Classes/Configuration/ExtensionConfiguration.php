<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Markus Klein <markus.klein@reelworx.at>
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

namespace BeechIt\FalSecuredownload\Configuration;

/**
 * Wrapper for the extension configuration
 */
class ExtensionConfiguration
{
    private static $isInitialized = false;
    private static $loginRedirectUrl = '';
    private static $noAccessRedirectUrl = '';
    private static $forceDownload = false;
    private static $forceDownloadForExt = '';
    private static $trackDownloads = false;
    private static $resumableDownload = true;

    private static function init()
    {
        if (!self::$isInitialized) {
            self::$isInitialized = true;

            $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['fal_securedownload']);
            self::$loginRedirectUrl = $extensionConfig['login_redirect_url'];
            self::$noAccessRedirectUrl = $extensionConfig['no_access_redirect_url'];
            self::$forceDownload = (bool)$extensionConfig['force_download'];
            self::$forceDownloadForExt = $extensionConfig['force_download_for_ext'];
            self::$trackDownloads = (bool)$extensionConfig['track_downloads'];
            self::$resumableDownload = (bool)(isset($extensionConfig['resumable_download']) ? $extensionConfig['resumable_download'] : false);
        }
    }

    /**
     * @return string
     */
    public static function loginRedirectUrl()
    {
        self::init();
        return self::$loginRedirectUrl;
    }

    /**
     * @return string
     */
    public static function noAccessRedirectUrl()
    {
        self::init();
        return self::$noAccessRedirectUrl;
    }

    /**
     * @return bool
     */
    public static function forceDownload()
    {
        self::init();
        return self::$forceDownload;
    }

    /**
     * @return string
     */
    public static function forceDownloadForExt()
    {
        self::init();
        return self::$forceDownloadForExt;
    }

    /**
     * Track user downloads
     *
     * @return bool
     */
    public static function trackDownloads()
    {
        self::init();
        return self::$trackDownloads;
    }

    /**
     * @return bool
     */
    public static function resumableDownload()
    {
        self::init();
        return self::$resumableDownload;
    }
}
