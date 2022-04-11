<?php

namespace BeechIt\FalSecuredownload\EventListener;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Frans Saris <frans@beech.it>
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

use BeechIt\FalSecuredownload\Aspects\PublicUrlAspect;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeneratePublicUrlForResourceEventListener
{
    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        if (!(Environment::isCli())) {
            $publicUrlAspect = GeneralUtility::makeInstance(PublicUrlAspect::class);
            $publicUrlAspect->generatePublicUrl($event->getStorage(), $event->getDriver(), $event->getResource(), $event->isRelativeToCurrentScript(), ['publicUrl' => $event->getPublicUrl()]);
        }
    }
}