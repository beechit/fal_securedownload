<?php
namespace BeechIt\FalSecuredownload\Controller;

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

use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * FileTreeController
 */
class FileTreeController extends ActionController
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
     * Render file tree
     */
    public function treeAction()
    {
        if ($this->settings['storage'] === '')
            return;
        try {
            $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($this->settings['storage'] . ':' . $this->settings['folder']);
        } catch (FolderDoesNotExistException $exception) {
            // folder not found
            $folder = null;
        }

        $this->view->assign('folder', $folder);
    }
}
