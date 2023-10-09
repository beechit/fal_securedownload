<?php

declare(strict_types=1);

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-03-2015 11:07
 * All code (c) Beech Applications B.V. all rights reserved
 */

namespace BeechIt\FalSecuredownload\EventListener;

use ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileMetaDataHasBeenRetrievedEvent;
use BeechIt\FalSecuredownload\Aspects\PublicUrlAspect;
use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterFileMetaDataHasBeenRetrievedEventListener implements SingletonInterface
{
    protected CheckPermissions $checkPermissionsService;
    protected PublicUrlAspect $publicUrlAspect;

    public function __construct()
    {
        $this->checkPermissionsService = GeneralUtility::makeInstance(CheckPermissions::class);
        $this->publicUrlAspect = GeneralUtility::makeInstance(PublicUrlAspect::class);
    }

    /**
     * Add correct fe_group info and public_url
     */
    public function __invoke(AfterFileMetaDataHasBeenRetrievedEvent $event)
    {
        $item = $event->getFileIndexQueueItem();
        $metadata = $event->getMetaData();

        if ($item->getFile() instanceof File && !$item->getFile()->getStorage()->isPublic()) {
            $resourcePermissions = $this->checkPermissionsService->getPermissions($item->getFile());
            // If there are already permissions set, refine these with actual file permissions
            if ($metadata['fe_groups']) {
                $metadata['fe_groups'] = implode(
                    ',',
                    ArrayUtility::keepItemsInArray(explode(',', $resourcePermissions), $metadata['fe_groups'])
                );
            } else {
                $metadata['fe_groups'] = $resourcePermissions;
            }
        }

        // Re-generate public url
        $this->publicUrlAspect->setEnabled(false);
        $metadata['public_url'] = $item->getFile()->getPublicUrl();
        $this->publicUrlAspect->setEnabled(true);

        $event->overrideMetaData($metadata);
    }
}
