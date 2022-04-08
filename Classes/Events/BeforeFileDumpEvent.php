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