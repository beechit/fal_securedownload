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

use TYPO3\CMS\Backend\ClickMenu\ClickMenu;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Add ClickMenuOptions in file list
 */
class ClickMenuOptions extends AbstractBeButtons
{
    /**
     * @var ClickMenu
     */
    protected $parentObject;

    /**
     * Add create tx_ icon to filemenu
     *
     * @param ClickMenu $parentObject Back-reference to the calling object
     * @param array $menuItems Current list of menu items
     * @param string $combinedIdentifier The combined identifier
     * @param integer $uid Id of the clicked on item
     * @return array Modified list of menu items
     */
    public function main(ClickMenu $parentObject, $menuItems, $combinedIdentifier, $uid)
    {

        if (!$parentObject->isDBmenu) {
            $this->parentObject = $parentObject;
            $combinedIdentifier = rawurldecode($combinedIdentifier);

            $extraMenuItems = $this->generateButtons($combinedIdentifier);
            if (count($extraMenuItems)) {
                $menuItems[] = 'spacer';
                $menuItems = array_merge($menuItems, $extraMenuItems);
            }
        }

        return $menuItems;
    }

    /**
     * Create click menu item
     *
     * @param string $title
     * @param string $shortTitle
     * @param string $icon
     * @param string $url
     * @param bool $addReturnUrl
     * @return string|array
     */
    protected function createLink($title, $shortTitle, $icon, $url, $addReturnUrl = true)
    {

        if (strpos($url, 'alert') !== 0) {
            $url = $this->parentObject->urlRefForCM($url, $addReturnUrl ? 'returnUrl' : '');
        }

        /** @var BackendUserAuthentication $beUser */
        $beUser = $GLOBALS['BE_USER'];

        return $this->parentObject->linkItem(
            '<span title="' . htmlspecialchars($title) . '">' . $shortTitle . '</span>',
            !empty($beUser->uc['noMenuMode']) && $beUser->uc['noMenuMode'] !== 'icons' ? '' : ' ' . $icon,
            $url
        );
    }
}
