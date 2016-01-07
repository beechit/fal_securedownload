<?php
namespace BeechIt\FalSecuredownload\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <franssaris@gmail.com>
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
 * Hook to add extra button to DocHeaderButtons in file list
 */
class DocHeaderButtonsHook extends AbstractBeButtons {

	/**
	 * Add folder permissions button to top bar of file list
	 *
	 * @param array $params ['buttons' => $buttons, 'markers' => &$markers, 'pObj' => &$this]
	 */
	public function addFolderPermissionsButton(array $params) {

		// only add button to file list module
		if ($params['pObj']->scriptID === 'ext/filelist/mod1/index.php') {
			$extraButtons = $this->generateButtons(GeneralUtility::_GP('id'));
			if (count($extraButtons)) {
				$params['markers']['BUTTONLIST_LEFT'] =
					preg_replace(
						'`</div>$`',
						implode('', $extraButtons) . '</div>',
						$params['markers']['BUTTONLIST_LEFT']
					);
			}
		}
	}

	/**
	 * Create button
	 *
	 * @param string $title
	 * @param string $shortTitle
	 * @param string $icon
	 * @param string $url
	 * @param bool $addReturnUrl
	 * @return string
	 */
	protected function createLink($title, $shortTitle, $icon, $url, $addReturnUrl = TRUE) {
		if (strpos($url, 'alert') === 0) {
			$url = 'javascript:' . $url;
		}
		$link = '';
		$link .= '<a href=\'' . $url . ($addReturnUrl ? '&returnUrl=' . rawurlencode($_SERVER['REQUEST_URI']) : '') . '\'';
		$link .= ' title="' . htmlspecialchars($title) . '">';
		$link .= $icon;
		$link .= '</a>';
		return $link;
	}
}