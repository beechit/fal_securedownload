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

namespace BeechIt\FalSecuredownload\Service;

use TYPO3\CMS\Core\Resource\Service\UserFileMountService as TYPO3UserFileMountService;

/**
 * FlexForm file mount service
 *
 * Registered in Configuration/FlexForms/FileTree.xml
 *
 * @noinspection PhpUnused
 */
class UserFileMountService extends TYPO3UserFileMountService
{

    /**
     * User function for to render a dropdown for selecting a folder of a selected storage
     *
     * @param array $PA the array with additional configuration options.
     * @noinspection PhpUnused
     */
    public function renderFlexFormSelectDropdown(array &$PA): void
    {
        $PA['row']['storage'] = $PA['row']['settings.storage'];
        parent::renderTceformsSelectDropdown($PA);
    }
}
