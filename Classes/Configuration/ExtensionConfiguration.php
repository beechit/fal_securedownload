<?php

declare(strict_types=1);

/*
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
 */

namespace BeechIt\FalSecuredownload\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Wrapper for the extension configuration
 */
class ExtensionConfiguration
{
    private static bool $isInitialized = false;
    private static string $loginRedirectUrl = '';
    private static string $noAccessRedirectUrl = '';
    private static bool $forceDownload = false;
    private static string $forceDownloadForExt = '';
    private static bool $trackDownloads = false;
    private static bool $resumableDownload = true;

    private static function init(): void
    {
        if (!self::$isInitialized) {
            self::$isInitialized = true;
            $extensionConfig = GeneralUtility::makeInstance(ExtensionConfigurationCore::class)->get('fal_securedownload');
            self::$loginRedirectUrl = $extensionConfig['login_redirect_url'];
            self::$noAccessRedirectUrl = $extensionConfig['no_access_redirect_url'];
            self::$forceDownload = (bool)$extensionConfig['force_download'];
            self::$forceDownloadForExt = $extensionConfig['force_download_for_ext'];
            self::$trackDownloads = (bool)$extensionConfig['track_downloads'];
            self::$resumableDownload = isset($extensionConfig['resumable_download']) && $extensionConfig['resumable_download'];
        }
    }

    public static function loginRedirectUrl(): string
    {
        self::init();
        return self::$loginRedirectUrl;
    }

    public static function noAccessRedirectUrl(): string
    {
        self::init();
        return self::$noAccessRedirectUrl;
    }

    public static function forceDownload(): bool
    {
        self::init();
        return self::$forceDownload;
    }

    public static function forceDownloadForExt(): string
    {
        self::init();
        return self::$forceDownloadForExt;
    }

    /**
     * Track user downloads
     */
    public static function trackDownloads(): bool
    {
        self::init();
        return self::$trackDownloads;
    }

    public static function resumableDownload(): bool
    {
        self::init();
        return self::$resumableDownload;
    }
}
