<?php

namespace BeechIt\FalSecuredownload\Events;

use BeechIt\FalSecuredownload\Hooks\FileDumpHook;
use TYPO3\CMS\Core\Resource\ResourceInterface;

final class BeforeFileDumpEvent
{
    private ResourceInterface $file;
    private FileDumpHook $caller;

    public function __construct(ResourceInterface $file, FileDumpHook $caller)
    {
        $this->file = $file;
        $this->caller = $caller;
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