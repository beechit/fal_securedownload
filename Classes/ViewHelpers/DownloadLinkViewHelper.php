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
 ***************************************************************/

namespace BeechIt\FalSecuredownload\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Download link view helper. Generates links that force a download action.
 *
 * @noinspection PhpUnused
 */
class DownloadLinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    public function __construct(private readonly HashService $hashService)
    {
        parent::__construct();
    }

    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'object', '', true);
        $this->registerArgument('uriOnly', 'bool', '', false, false);
    }

    /**
     * Create a link to a file that forces a download
     *
     * @return string
     */
    public function render(): string
    {
        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $queryParameterArray = ['eID' => 'dumpFile', 't' => ''];
        if ($file instanceof File) {
            $queryParameterArray['f'] = $file->getUid();
            $queryParameterArray['t'] = 'f';
        } elseif ($file instanceof ProcessedFile) {
            $queryParameterArray['p'] = $file->getUid();
            $queryParameterArray['t'] = 'p';
        }

        $queryParameterArray['token'] = $this->hashService->hmac(implode('|', $queryParameterArray), 'resourceStorageDumpFile', HashAlgo::SHA3_256);
        $queryParameterArray['download'] = '';
        $uri = 'index.php?' . str_replace('+', '%20', http_build_query($queryParameterArray));

        // Add absRefPrefix
        $uri = $this->getUrlPrefix() . $uri;

        if ($this->arguments['uriOnly']) {
            return $uri;
        }

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }

    /**
     * Determine "config.absRefPrefix" from the TypoScript of the current frontend request
     */
    protected function getUrlPrefix(): string
    {
        if (!$this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return '';
        }
        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        try {
            $typoScriptConfigArray = $request->getAttribute('frontend.typoscript')?->getConfigArray() ?? [];
        } catch (\RuntimeException) {
            return '';
        }
        $absRefPrefix = trim($typoScriptConfigArray['absRefPrefix'] ?? '');
        if ($absRefPrefix === 'auto') {
            $absRefPrefix = $request->getAttribute('normalizedParams')?->getSitePath() ?? '';
        }
        return $absRefPrefix;
    }
}
