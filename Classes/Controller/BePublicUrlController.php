<?php

declare(strict_types=1);

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 22-08-2014 16:04
 * All code (c) Beech Applications B.V. all rights reserved
 */

namespace BeechIt\FalSecuredownload\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\AbstractApplication;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Ajax controller for public url in BE
 */
class BePublicUrlController extends AbstractApplication
{
    protected ResourceFactory $resourceFactory;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(ResourceFactory $resourceFactory = null, ResponseFactoryInterface $responseFactory)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
        $this->responseFactory = $responseFactory;
    }

    /**
     * Dump file content
     */
    public function dumpFile(): ResponseInterface
    {
        $parameters = ['eID' => 'dumpFile'];
        if (GeneralUtility::_GP('t')) {
            $parameters['t'] = GeneralUtility::_GP('t');
        }
        if (GeneralUtility::_GP('f')) {
            $parameters['f'] = (int)GeneralUtility::_GP('f');
        }
        if (GeneralUtility::_GP('p')) {
            $parameters['p'] = (int)GeneralUtility::_GP('p');
        }

        if (
            GeneralUtility::hmac(
                implode('|', $parameters), 'BeResourceStorageDumpFile'
            ) === GeneralUtility::_GP('fal_token')
        ) {
            if (isset($parameters['f'])) {
                $file = $this->resourceFactory->getFileObject($parameters['f']);
                if ($file->isDeleted() || $file->isMissing()) {
                    $file = null;
                }
                $orgFile = $file;
            } else {
                /** @var ProcessedFile $file */
                $file = GeneralUtility::makeInstance(ProcessedFileRepository::class)->findByUid($parameters['p']);
                if ($file->isDeleted()) {
                    return $this->responseFactory->createResponse(404);
                }
                $orgFile = $file->getOriginalFile();
            }

            // Check file read permissions
            if (!$orgFile->getStorage()->checkFileActionPermission('read', $orgFile)) {
                return $this->responseFactory->createResponse(403);
            }

            if ($file === null) {
                return $this->responseFactory->createResponse(404);;
            }

            ob_start();

            $response = $file->getStorage()->streamFile($file);
            $this->sendResponse($response);

            exit;
        }
        return $this->responseFactory->createResponse(403);
    }
}
