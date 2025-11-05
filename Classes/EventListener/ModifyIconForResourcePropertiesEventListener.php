<?php

declare(strict_types=1);

/*
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
 */

namespace BeechIt\FalSecuredownload\EventListener;

use BeechIt\FalSecuredownload\Aspects\IconFactoryAspect;
use TYPO3\CMS\Core\Imaging\Event\ModifyIconForResourcePropertiesEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * EventListener is registered in Services.yaml
 *
 * @noinspection PhpUnused
 */
class ModifyIconForResourcePropertiesEventListener
{
    public function __invoke(ModifyIconForResourcePropertiesEvent $event): void
    {
        $iconFactoryAspect = GeneralUtility::makeInstance(IconFactoryAspect::class);

        [, , , $iconIdentifier, $overlayIdentifier]
            = $iconFactoryAspect->buildIconForResource(
                $event->getResource(),
                $event->getSize(),
                $event->getOptions(),
                $event->getIconIdentifier(),
                $event->getOverlayIdentifier()
            );
        $event->setOverlayIdentifier($overlayIdentifier);
        $event->setIconIdentifier($iconIdentifier);
    }
}
