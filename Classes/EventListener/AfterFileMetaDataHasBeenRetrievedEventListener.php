<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace BeechIt\FalSecuredownload\EventListener;

use BeechIt\FalSecuredownload\Aspects\SolrFalAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AfterFileMetaDataHasBeenRetrievedEventListener
    {

    protected SolrFalAspect $solrFalAspect;

    public function __construct()
        {
        $this->solrFalAspect = GeneralUtility::makeInstance( SolrFalAspect::class);
        }
    public function __invoke(\ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileMetaDataHasBeenRetrievedEvent $event): void
        {

        $metaDataArrayObject = new \ArrayObject( $event->getMetaData() );
        $item                = $event->getFileIndexQueueItem();
        $this->solrFalAspect->fileMetaDataRetrieved( $item, $metaDataArrayObject );
        }
    }