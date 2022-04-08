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

    public function getLoginRedirectUrl(): ?string
    {
        return $this->loginRedirectUrl;
    }

    public function setLoginRedirectUrl(?string $loginRedirectUrl): void
    {
        $this->loginRedirectUrl = $loginRedirectUrl;
    }

    public function getNoAccessRedirectUrl(): ?string
    {
        return $this->noAccessRedirectUrl;
    }

    public function setNoAccessRedirectUrl(?string $noAccessRedirectUrl): void
    {
        $this->noAccessRedirectUrl = $noAccessRedirectUrl;
    }

    public function getFile(): ResourceInterface
    {
        return $this->file;
    }

    public function setFile(ResourceInterface $file): void
    {
        $this->file = $file;
    }

    public function getCaller(): FileDumpHook
    {
        return $this->caller;
    }

    public function setCaller(FileDumpHook $caller): void
    {
        $this->caller = $caller;
    }



}