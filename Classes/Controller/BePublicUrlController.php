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
    private ProcessedFileRepository $processedFileRepository;

    public function __construct(
        ResourceFactory $resourceFactory,
        ResponseFactoryInterface $responseFactory,
        ProcessedFileRepository $processedFileRepository
    )
    {
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
                try {
                    $file = $this->resourceFactory->getFileObject($parameters['f']);
                } catch (FileDoesNotExistException $e) {
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
                } catch (RuntimeException $e) {
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
