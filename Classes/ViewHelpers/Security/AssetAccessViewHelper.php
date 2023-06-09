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

namespace BeechIt\FalSecuredownload\ViewHelpers\Security;

use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Registered as ViewHelper in fluid templates
 *
 * @noinspection PhpUnused
 */
class AssetAccessViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('folder', 'object', '', true);
        $this->registerArgument('file', 'object', '');
    }

    /**
     * renders <f:then> child if the current logged in FE user has access to the given asset
     * otherwise renders <f:else> child.
     *
     * @return string
     * @throws AspectNotFoundException
     */
    public function render(): string
    {
        return self::evaluateCondition($this->arguments) ? $this->renderThenChild() : $this->renderElseChild();
    }

    /**
     * Evaluate access
     *
     * @param array $arguments
     * @return bool
     * @throws AspectNotFoundException
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        /** @var Folder $folder */
        $folder = $arguments['folder'];
        /** @var File $file */
        $file = $arguments['file'];

        /** @var $checkPermissionsService CheckPermissions */
        $checkPermissionsService = GeneralUtility::makeInstance(CheckPermissions::class);
        $userFeGroups = self::getFeUserGroups();
        $access = false;

        // check folder access
        if ($checkPermissionsService->checkFolderRootLineAccess($folder, $userFeGroups)) {
            if ($file === null) {
                $access = true;
            } else {
                $feGroups = $file->getProperty('fe_groups');
                if ((string)$feGroups !== '') {
                    $access = $checkPermissionsService->matchFeGroupsWithFeUser($feGroups, $userFeGroups);
                } else {
                    $access = true;
                }
            }
        }

        return $access;
    }

    /**
     * Determines whether the currently logged in FE user belongs to the specified usergroup
     *
     * @return bool|array FALSE when not logged in or else frontend.user.groupIds
     * @throws AspectNotFoundException
     */
    protected static function getFeUserGroups()
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        if (!$context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
            return false;
        }
        return $context->getPropertyFromAspect('frontend.user', 'groupIds');
    }
}
