<?php
namespace BeechIt\FalSecuredownload\Security;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\PathUtility;
/**
 * Generate special public url for files in non-public storages
 *
 * todo: remove when https://review.typo3.org/#/c/27760/ is in and create a hook for eID dumpFile
 */
class PublicUrlAspect {

	/**
	 * Generate public url for file
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param $relativeToCurrentScript
	 * @param array $urlData
	 * @return void
	 */
	public function generatePublicUrl(\TYPO3\CMS\Core\Resource\ResourceStorage $storage, \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver, \TYPO3\CMS\Core\Resource\FileInterface $file, $relativeToCurrentScript, array $urlData) {

		if ($this->urlSubstitutionNeeded($storage, $driver, $file)) {

			// create public url
			$publicUrl = 'index.php?type=1337';

			$identifier = ($file instanceof ProcessedFile ? 'p' : 'f').':'.$file->getUid();
			$hash = GeneralUtility::hmac($identifier, 'fal_securedownload');

			$publicUrl .= '&h='.$hash;
			$publicUrl .= '&file='.$identifier;

			// If requested, make the path relative to the current script in order to make it possible
			// to use the relative file
			if ($relativeToCurrentScript) {
				$publicUrl = PathUtility::getRelativePathTo(PathUtility::dirname((PATH_site . $publicUrl))) . PathUtility::basename($publicUrl);
			}
			// $urlData['publicUrl'] is passed by reference, so we can change that here and the value will be taken into account
			$urlData['publicUrl'] =  $publicUrl;
		}
	}

	/**
	 * Check if url needs to be substituted
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return bool
	 */
	protected function urlSubstitutionNeeded(\TYPO3\CMS\Core\Resource\ResourceStorage $storage, \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver, \TYPO3\CMS\Core\Resource\FileInterface $file) {

		// force storage isPublic check for now
		// todo: move this to extension settings
		if (TRUE) {
			return !$storage->isPublic();
		} else {
			return $this->urlAccessible($driver->getPublicUrl($file->getIdentifier()));
		}
	}

	/**
	 * Check if file is direct accessible through browser
	 * @param $url
	 * @return bool
	 */
	protected function urlAccessible($url) {
		if (strpos($url, 'http') === FALSE) {
			$url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL').$url;
		}
		$handle   = curl_init($url);
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
		curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox
		curl_setopt($handle, CURLOPT_NOBODY, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
		$connectAble = curl_exec($handle);
		curl_close($handle);
		return (bool) $connectAble;
	}
}