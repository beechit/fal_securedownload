<?php
namespace BeechIt\FalSecuredownload\Security;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\PathUtility;
/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 18-02-2014 09:08
 * All code (c) Beech Applications B.V. all rights reserved
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
			// todo: move type as setting to extension configuration
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
		$recursive = FALSE;
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