<?php

namespace BeechIt\FalSecuredownload\EventListener;

use BeechIt\FalSecuredownload\Aspects\IconFactoryAspect;
use TYPO3\CMS\Core\Imaging\Event\ModifyIconForResourcePropertiesEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModifyIconForResourcePropertiesEventListener
{
    public function __invoke(ModifyIconForResourcePropertiesEvent $event): void
    {
        $iconFactoryAspect = GeneralUtility::makeInstance(IconFactoryAspect::class);

        List($resource, $size, $options, $iconIdentifier, $overlayIdentifier) =
            $iconFactoryAspect->buildIconForResource(
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
