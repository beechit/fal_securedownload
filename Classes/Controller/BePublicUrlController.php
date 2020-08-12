<?php
namespace BeechIt\FalSecuredownload\Controller;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 22-08-2014 16:04
 * All code (c) Beech Applications B.V. all rights reserved
 */

use TYPO3\CMS\Core\Http\AbstractApplication;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Ajax controller for public url in BE
 */
class BePublicUrlController extends AbstractApplication
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @param ResourceFactory $resourceFactory
     */
    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Dump file content
     * @return void
     */
    public function dumpFile()
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

        if (GeneralUtility::hmac(
            implode('|', $parameters),
            'BeResourceStorageDumpFile'
        ) === GeneralUtility::_GP('fal_token')
        ) {
            if (isset($parameters['f'])) {
                $file = $this->resourceFactory->getFileObject($parameters['f']);
                if ($file->isDeleted() || $file->isMissing()) {
                    $file = null;
                }
                $orgFile = $file;
            } else {
                /** @var \TYPO3\CMS\Core\Resource\ProcessedFile $file */
                $file = GeneralUtility::makeInstance(ProcessedFileRepository::class)->findByUid($parameters['p']);
                if ($file->isDeleted()) {
                    HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_404);
                }
                $orgFile = $file->getOriginalFile();
            }

            // Check file read permissions
            if (!$orgFile->getStorage()->checkFileActionPermission('read', $orgFile)) {
                HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_403);
            }

            if ($file === null) {
                HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_404);
            }

            ob_start();

            $response = $file->getStorage()->streamFile($file);
            $this->sendResponse($response);

            exit;
        }
        HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_403);
    }
}
