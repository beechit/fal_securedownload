<?php
namespace BeechIt\FalSecuredownload\ViewHelpers;

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

/**
 * Download link view helper. Generates links that force a download action.
 */
class DownloadLinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\ExternalViewHelper {

	/**
	 * Create a link to a file that forces a download
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return string
	 */
	public function render(\TYPO3\CMS\Core\Resource\FileInterface $file) {

		$queryParameterArray = array('eID' => 'dumpFile', 't' => '');
		if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
			$queryParameterArray['f'] = $file->getUid();
			$queryParameterArray['t'] = 'f';
		} elseif ($file instanceof \TYPO3\CMS\Core\Resource\ProcessedFile) {
			$queryParameterArray['p'] = $file->getUid();
			$queryParameterArray['t'] = 'p';
		}

		$queryParameterArray['token'] = \TYPO3\CMS\Core\Utility\GeneralUtility::hmac(implode('|', $queryParameterArray), 'resourceStorageDumpFile');
		$queryParameterArray['download'] = '';
		$uri = 'index.php?' . str_replace('+', '%20', http_build_query($queryParameterArray));

		$this->tag->addAttribute('href', $uri);
		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);

		return $this->tag->render();
	}
}