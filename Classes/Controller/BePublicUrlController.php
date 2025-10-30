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
use RuntimeException;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\AbstractApplication;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
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
    private readonly ProcessedFileRepository $processedFileRepository;

    public function __construct(
        ResourceFactory $resourceFactory,
        ResponseFactoryInterface $responseFactory,
        ProcessedFileRepository $processedFileRepository
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->responseFactory = $responseFactory;
        $this->processedFileRepository = $processedFileRepository;
    }

    /**
     * Dump file content
     */
    public function dumpFile(): ResponseInterface
    {
        $parameters = ['eID' => 'dumpFile'];
        if ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['t'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['t'] ?? null) {
            $parameters['t'] = $GLOBALS['TYPO3_REQUEST']->getParsedBody()['t'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['t'] ?? null;
        }
        if ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['f'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['f'] ?? null) {
            $parameters['f'] = (int)($GLOBALS['TYPO3_REQUEST']->getParsedBody()['f'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['f'] ?? null);
        }
        if ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['p'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['p'] ?? null) {
            $parameters['p'] = (int)($GLOBALS['TYPO3_REQUEST']->getParsedBody()['p'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['p'] ?? null);
        }

        if (
            GeneralUtility::makeInstance(HashService::class)->hmac(implode('|', $parameters), 'BeResourceStorageDumpFile') === ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['fal_token'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['fal_token'] ?? null)
        ) {
            if (isset($parameters['f'])) {
                try {
                    $file = $this->resourceFactory->getFileObject($parameters['f']);
                } catch (FileDoesNotExistException) {
                    return $this->responseFactory->createResponse(404);
                }
                if ($file->isDeleted() || $file->isMissing()) {
                    return $this->responseFactory->createResponse(404);
                }
                $orgFile = $file;
            } else {
                try {
                    /** @var ProcessedFile $file */
                    $file = $this->processedFileRepository->findByUid($parameters['p']);
                } catch (RuntimeException) {
                    return $this->responseFactory->createResponse(404);
                }
                if ($file->isDeleted()) {
                    return $this->responseFactory->createResponse(404);
                }
                $orgFile = $file->getOriginalFile();
            }

            // Check file read permissions
            if (!$orgFile->getStorage()->checkFileActionPermission('read', $orgFile)) {
                return $this->responseFactory->createResponse(403);
            }

            ob_start();

            $response = $file->getStorage()->streamFile($file);
            $this->sendResponse($response);

            exit;
        }
        return $this->responseFactory->createResponse(403);
    }
}
