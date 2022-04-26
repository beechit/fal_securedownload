<?php

namespace BeechIt\FalSecuredownload\Events;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Frans Saris <frans@beech.it>
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
 ***************************************************************/

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