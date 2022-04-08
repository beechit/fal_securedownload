<?php

namespace BeechIt\FalSecuredownload\Events;

use BeechIt\FalSecuredownload\Hooks\FileDumpHook;
use TYPO3\CMS\Core\Resource\ResourceInterface;

final class BeforeRedirectsEvent
{
    private ?string $loginRedirectUrl;
    private ?string $noAccessRedirectUrl;
    private ResourceInterface $file;
    private FileDumpHook $caller;

    public function __construct(?string $loginRedirectUrl, ?string $noAccessRedirectUrl, ResourceInterface $file, FileDumpHook $caller)
    {
        $this->loginRedirectUrl = $loginRedirectUrl;
        $this->noAccessRedirectUrl = $noAccessRedirectUrl;
        $this->file = $file;
        $this->caller = $caller;
    }

    /**
     * @return string|null
     */
    public function getLoginRedirectUrl(): ?string
    {
        return $this->loginRedirectUrl;
    }

    /**
     * @param string|null $loginRedirectUrl
     */
    public function setLoginRedirectUrl(?string $loginRedirectUrl): void
    {
        $this->loginRedirectUrl = $loginRedirectUrl;
    }

    /**
     * @return string|null
     */
    public function getNoAccessRedirectUrl(): ?string
    {
        return $this->noAccessRedirectUrl;
    }

    /**
     * @param string|null $noAccessRedirectUrl
     */
    public function setNoAccessRedirectUrl(?string $noAccessRedirectUrl): void
    {
        $this->noAccessRedirectUrl = $noAccessRedirectUrl;
    }

    /**
     * @return ResourceInterface
     */
    public function getFile(): ResourceInterface
    {
        return $this->file;
    }

    /**
     * @param ResourceInterface $file
     */
    public function setFile(ResourceInterface $file): void
    {
        $this->file = $file;
    }

    /**
     * @return FileDumpHook
     */
    public function getCaller(): FileDumpHook
    {
        return $this->caller;
    }

    /**
     * @param FileDumpHook $caller
     */
    public function setCaller(FileDumpHook $caller): void
    {
        $this->caller = $caller;
    }



}