<?php
namespace BeechIt\FalSecuredownload\ViewHelpers;

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

use BeechIt\FalSecuredownload\Service\LeafStateService;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class LeaveStateViewHelper
 *
 * @package BeechIt\FalSecuredownload\ViewHelpers
 */
class LeaveStateViewHelper extends AbstractConditionViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('folder', 'object', '', true);
    }

    /**
     * renders <f:then> child if the current visitor ...
     * otherwise renders <f:else> child.

     * @return string
     */
    public function render()
    {
        /** @var Folder $folder */
        $folder = $this->arguments['folder'];

        $leafStateService = GeneralUtility::makeInstance(LeafStateService::class);
        $feUser = !empty($GLOBALS['TSFE']) ? $GLOBALS['TSFE']->fe_user : false;

        if ($feUser && $leafStateService->getLeafStateForUser($feUser, $folder->getCombinedIdentifier())) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }
}
