<?php

namespace BeechIt\FalSecuredownload\EventListener;

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