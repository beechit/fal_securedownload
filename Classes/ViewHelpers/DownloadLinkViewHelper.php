<?php
namespace BeechIt\FalSecuredownload\ViewHelpers;

/***************************************************************
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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Download link view helper. Generates links that force a download action.
 */
class DownloadLinkViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document');
        $this->registerTagAttribute('rev', 'string', 'Specifies the relationship between the linked document and the current document');
        $this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
        $this->registerArgument('file', 'object', '', true);
        $this->registerArgument('uriOnly', 'bool', '', false, false);
    }

    /**
     * Create a link to a file that forces a download
     *
     * @return string
     */
    public function render()
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

        $queryParameterArray['token'] = GeneralUtility::hmac(implode('|', $queryParameterArray),
            'resourceStorageDumpFile');
        $queryParameterArray['download'] = '';
        $uri = 'index.php?' . str_replace('+', '%20', http_build_query($queryParameterArray));

        // Add absRefPrefix
        if (!empty($GLOBALS['TSFE'])) {
            $uri = $GLOBALS['TSFE']->absRefPrefix . $uri;
        }

        if ($this->arguments['uriOnly']) {
            return $uri;
        }

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}