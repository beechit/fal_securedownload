<?php

declare(strict_types=1);

/*
 *  Copyright notice
 *
 *  (c) 2014 Frans Saris <frans@beech.it>
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

use BeechIt\FalSecuredownload\Configuration\ExtensionConfiguration;
use BeechIt\FalSecuredownload\Context\UserAspect;
use BeechIt\FalSecuredownload\Events\BeforeFileDumpEvent;
use BeechIt\FalSecuredownload\Events\BeforeRedirectsEvent;
use BeechIt\FalSecuredownload\Security\CheckPermissions;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\Event\ModifyFileDumpEvent;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ModifyFileDumpEventListener
{
    protected ?FrontendUserAuthentication $feUser = null;
    protected FileInterface $originalFile;
    protected string $loginRedirectUrl = '';
    protected string $noAccessRedirectUrl = '';
    protected bool $forceDownload = false;
    protected string $forceDownloadForExt = '';
    protected bool $resumableDownload = false;
    protected Context $context;
    private readonly EventDispatcherInterface $eventDispatcher;
    private ModifyFileDumpEvent $event;

    public function __construct(EventDispatcherInterface $eventDispatcher, private readonly ConnectionPool $connectionPool)
    {
        $this->context = GeneralUtility::makeInstance(Context::class);

        if (ExtensionConfiguration::loginRedirectUrl()) {
            $this->loginRedirectUrl = ExtensionConfiguration::loginRedirectUrl();
        }
        if (ExtensionConfiguration::noAccessRedirectUrl()) {
            $this->noAccessRedirectUrl = ExtensionConfiguration::noAccessRedirectUrl();
        }
        $this->forceDownload = ExtensionConfiguration::forceDownload();
        if (ExtensionConfiguration::forceDownloadForExt()) {
            $this->forceDownloadForExt = ExtensionConfiguration::forceDownloadForExt();
        }
        $this->resumableDownload = ExtensionConfiguration::resumableDownload();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(ModifyFileDumpEvent $event): void
    {
        $this->event = $event;
        $this->checkFileAccess($event->getFile());
    }

    /**
     * @see https://github.com/beechit/fal_securedownload/issues/37
     * @noinspection PhpUnused
     */
    public function getFeUser(): FrontendUserAuthentication
    {
        return $this->feUser;
    }

    /**
     * Perform custom security/access when accessing file
     * Method should issue 403 if access is rejected
     * or 401 if authentication is required
     *
     * @param ResourceInterface $file
     */
    private function checkFileAccess(ResourceInterface $file): void
    {
        if (!$file instanceof FileInterface) {
            throw new RuntimeException('Given $file is not a file.', 1469019515);
        }
        if (method_exists($file, 'getOriginalFile')) {
            $this->originalFile = $file->getOriginalFile();
        } else {
            $this->originalFile = $file;
        }

        $loginRedirectUrl = $this->loginRedirectUrl;
        $noAccessRedirectUrl = $this->noAccessRedirectUrl;

        $beforeRedirectsEvent = $this->eventDispatcher->dispatch(new BeforeRedirectsEvent($loginRedirectUrl, $noAccessRedirectUrl, $file, $this));
        $loginRedirectUrl = $beforeRedirectsEvent->getLoginRedirectUrl();
        $noAccessRedirectUrl = $beforeRedirectsEvent->getNoAccessRedirectUrl();

        if (!$this->checkPermissions()) {
            if (!$this->isLoggedIn()) {
                if (!empty($loginRedirectUrl)) {
                    $this->redirectToUrl($loginRedirectUrl);
                } else {
                    $this->exitScript('Authentication required!');
                }
            } elseif (!empty($noAccessRedirectUrl)) {
                $this->redirectToUrl($noAccessRedirectUrl);
            } else {
                $this->exitScript('No access!');
            }
        }
        $this->eventDispatcher->dispatch(new BeforeFileDumpEvent($file, $this));

        if (ExtensionConfiguration::trackDownloads()) {
            $columns = [
                'tstamp' => time(),
                'crdate' => time(),
                'feuser' => (int)$this->feUser->user['uid'],
                'file' => (int)$this->originalFile->getUid(),
            ];

            $this->connectionPool
                ->getConnectionForTable('tx_falsecuredownload_download')
                ->insert(
                    'tx_falsecuredownload_download',
                    $columns,
                    [Connection::PARAM_INT, Connection::PARAM_INT, Connection::PARAM_INT, Connection::PARAM_INT]
                );
        }

        // Dump the precise requested file for File and ProcessedFile, but dump the referenced file for FileReference
        $dumpFile = $file instanceof FileReference ? $file->getOriginalFile() : $file;

        if ($this->forceDownload($dumpFile->getExtension())) {
            $this->dumpFileContents($dumpFile, true, $this->resumableDownload);
        } elseif ($this->resumableDownload) {
            $this->dumpFileContents($dumpFile, false, true);
        }
    }

    /**
     * Dump file contents
     *
     * TODO: Try to get the resumable option part of TYPO3 core itself find a nicer way to force the download. Other hooks are blocked by this.
     *
     * @param FileInterface $file
     * @param bool $asDownload
     * @param bool $resumableDownload
     */
    protected function dumpFileContents(FileInterface $file, bool $asDownload, bool $resumableDownload)
    {
        $downloadName = $file->hasProperty('download_name') && $file->getProperty('download_name') ? $file->getProperty('download_name') : $file->getName();

        // Make sure downloadName has a file extension
        $fileParts = pathinfo($downloadName);
        if (empty($fileParts['extension'])) {
            $downloadName .= '.' . $file->getExtension();
        }

        if (!$resumableDownload) {
            $response = $file->getStorage()->streamFile($file, $asDownload, $downloadName);
            $this->event->setResponse($response);
            exit;
        }

        $contentDisposition = $asDownload ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $contentDisposition . '; filename="' . $downloadName . '"');
        header('Content-Type: ' . $file->getMimeType());
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');

        $fileSize = $file->getSize();
        $range = $this->getHttpRange($fileSize);
        if ($range === []) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        // Find part of file and push this out
        $filePointer = @fopen($file->getForLocalProcessing(false), 'rb');
        if ($filePointer === false) {
            header('HTTP/1.1 404 File not found');
            exit;
        }

        $dumpSize = $fileSize;
        [$begin, $end] = $range;
        if ($begin !== 0 || $end !== $fileSize - 1) {
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $begin . '-' . $end . '/' . $fileSize);
            $dumpSize = $end - $begin + 1;
        }
        header('Content-Length: ' . $dumpSize);
        header('Accept-Ranges: bytes');

        ob_clean();
        flush();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        fseek($filePointer, $begin);
        $dumpedSize = 0;
        while (!feof($filePointer) && $dumpedSize < $dumpSize) {
            $partSize = 1024 * 8;
            if ($partSize > $dumpSize - $dumpedSize) {
                $partSize = $dumpSize - $dumpedSize;
            }
            $buffer = @fread($filePointer, $partSize);
            $dumpedSize += strlen($buffer);
            print $buffer;
            flush();

            if (connection_status() !== 0) {
                break;
            }
        }

        @fclose($filePointer);
        exit;
    }

    /**
     * Determine if we want to force a file download
     */
    protected function forceDownload(string $fileExtension): bool
    {
        $forceDownload = false;
        if ($this->forceDownload) {
            $forceDownload = true;
        } elseif (isset($_REQUEST['download'])) {
            $forceDownload = true;
        } elseif (GeneralUtility::inList(str_replace(' ', '', $this->forceDownloadForExt), $fileExtension)) {
            $forceDownload = true;
        }

        return $forceDownload;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        try {
            $this->initializeUserAuthentication();
            return is_array($this->feUser->user) && $this->feUser->user['uid'];
        } catch (AspectNotFoundException|AspectPropertyNotFoundException) {
            return false;
        }
    }

    /**
     * Check if current user has enough permissions to view file
     */
    protected function checkPermissions(): bool
    {
        try {
            $this->initializeUserAuthentication();
        } catch (AspectNotFoundException|AspectPropertyNotFoundException) {
            return false;
        }

        /** @var $checkPermissionsService CheckPermissions */
        $checkPermissionsService = GeneralUtility::makeInstance(CheckPermissions::class);

        if ($checkPermissionsService->checkBackendUserFileAccess($this->originalFile)) {
            return true;
        }

        // The CheckPermissions service receives the current user's groups as input to
        // ultimately compare them against the file's permissions. If the user isn't
        // logged in at all, "false" is passed instead. There are two possible ways to
        // interpret the user data:
        // 1. If a user isn't logged in, the groups don't matter at all. This would
        //    ignore groups being added with the ModifyResolvedFrontendGroupsEvent.
        // 2. If a user has any groups, regardless of login status, those groups will
        //    be used for the permission check.
        // Variant 2 is implemented here.
        $userFeGroups = !$this->isLoggedIn() && $this->feUser->groupData['uid'] === []
            ? false
            : $this->feUser->groupData['uid'];

        try {
            return $checkPermissionsService->checkFileAccess($this->originalFile, $userFeGroups);
        } catch (FolderDoesNotExistException) {
            return false;
        }
    }

    /**
     * Initialize feUser
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    protected function initializeUserAuthentication()
    {
        if ($this->feUser === null) {
            /** @var UserAspect $userAspect */
            $userAspect = $this->context->getAspect('beechit.user');
            $this->feUser = $userAspect->get('user');
            $this->feUser->fetchGroupData($this->event->getRequest());
        }
    }

    /**
     * Exit with an error message
     */
    protected function exitScript(string $message, int $httpCode = 403): never
    {
        header('HTTP/1.1 ' . $httpCode . ' Forbidden');
        exit($message);
    }

    /**
     * Redirect to url
     */
    protected function redirectToUrl(string $url)
    {
        $url = str_replace(
            '###REQUEST_URI###',
            rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')),
            $url
        );

        if (stripos($url, 't3://') === 0) {
            $url = $this->resolveUrl($url);
        }

        header('location: ' . $url);
        exit;
    }

    /**
     * Resolve the URL (currently only page and external URL are supported)
     */
    protected function resolveUrl(string $url): string
    {
        try {
            $urlParameters = GeneralUtility::makeInstance(LinkService::class)->resolve($url);
        } catch (UnknownLinkHandlerException) {
            throw new InvalidArgumentException(
                'Redirects URL can only handle TYPO3 urls of types "page" or "url".',
                1686123053
            );
        }

        if ($urlParameters['type'] !== LinkService::TYPE_PAGE && $urlParameters['type'] !== LinkService::TYPE_URL) {
            throw new InvalidArgumentException(
                'Redirects URL can only handle TYPO3 urls of types "page" or "url".',
                1522826609
            );
        }

        if ($urlParameters['type'] === LinkService::TYPE_URL) {
            $uri = $urlParameters['url'];
        } else {
            /** @var ContentObjectRenderer $contentObject */
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class, null);
            $contentObject->start([], '');

            $uri = $contentObject->typoLink_URL([
                'addQueryString' => true,
                'addQueryString.' => [
                    'exclude' => 'eID,f,t,token',
                ],
                'forceAbsoluteUrl' => true,
                'parameter' => $url,
                'returnLast' => 'url',
            ]);
        }

        return (string)$uri;
    }

    /**
     * Determines the HTTP range given in the request
     *
     * @param int $fileSize the size of the file
     * @return array the range (begin, end), or empty array if the range request is invalid.
     */
    protected function getHttpRange(int $fileSize): array
    {
        $range = $_SERVER['HTTP_RANGE'] ?? false;
        if (!$range || $range === '-') {
            return [0, $fileSize - 1];
        }
        if (!preg_match('/^bytes=(\d*)-(\d*)$/', $range, $matches)) {
            return [];
        }
        if ($matches[1] === '') {
            $start = $fileSize - $matches[2];
            $end = $fileSize - 1;
        } elseif ($matches[2] !== '') {
            $start = $matches[1];
            $end = $matches[2];
            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }
        } else {
            $start = $matches[1];
            $end = $fileSize - 1;
        }
        if ($start < 0 || $start > $end) {
            return [];
        }
        return [(int)$start, (int)$end];
    }
}
