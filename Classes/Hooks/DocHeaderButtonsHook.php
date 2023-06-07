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

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to add extra button to DocHeaderButtons in file list
 */
class DocHeaderButtonsHook extends AbstractBeButtons
{
    /**
     * Add folder permissions button to top bar of file list
     *
     * @param array $params ['buttons' => $buttons, 'markers' => &$markers, 'pObj' => &$this]
     * @throws RouteNotFoundException
     */
    public function addFolderPermissionsButton(array $params)
    {
        // only add button to file list module
        if ($params['pObj']->scriptID === 'ext/filelist/mod1/index.php') {
            $extraButtons = $this->generateButtons(GeneralUtility::_GP('id'));
            if (!empty($extraButtons)) {
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
     */
    protected function createLink(string $title, string $shortTitle, Icon $icon, UriInterface $url, bool $addReturnUrl = true): array
    {
        return [
            'title' => $title,
            'icon' => $icon,
            'url' => $url . ($addReturnUrl ? '&returnUrl=' . rawurlencode($_SERVER['REQUEST_URI']) : '')
        ];
    }

    /**
     * Get buttons
     *
     * Registered in ext_localconf.php as ['Backend\Template\Components\ButtonBar']['getButtonsHook']
     *
     * @throws RouteNotFoundException
     */
    public function getButtons(array $params, ButtonBar $buttonBar): array
    {
        $buttons = $params['buttons'];

        foreach ($this->generateButtons(GeneralUtility::_GP('id')) as $buttonInfo) {
            $button = $buttonBar->makeLinkButton();
            $button->setIcon($buttonInfo['icon']);
            $button->setTitle($buttonInfo['title']);
            $button->setHref($buttonInfo['url']);
            $buttons[ButtonBar::BUTTON_POSITION_LEFT][1][] = $button;
        }

        return $buttons;
    }
}
