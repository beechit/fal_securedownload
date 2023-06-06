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

namespace BeechIt\FalSecuredownload\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FileTreeController extends ActionController
{

    protected ResourceFactory $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Render file tree
     */
    public function treeAction(): ResponseInterface
    {
        if ($this->settings['storage'] === '') {
            return $this->htmlResponse('Storage is not configured');
        }

        try {
            $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($this->settings['storage'] . ':' . $this->settings['folder']);
        } catch (FolderDoesNotExistException $exception) {
            // folder not found
            $folder = null;
        }

        $this->view->assign('folder', $folder);

        return $this->htmlResponse();
    }
}
