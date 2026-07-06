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

use BeechIt\FalSecuredownload\Hooks\AbstractBeButtons;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Adds the FolderPermission button to the doc header of the file list
 *
 * EventListener is registered in Services.yaml
 *
 * @noinspection PhpUnused
 */
class ModifyButtonBarEventListener extends AbstractBeButtons
{
    public function __construct(
        ?ResourceFactory $resourceFactory,
        private readonly ComponentFactory $componentFactory
    ) {
        parent::__construct($resourceFactory);
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $identifier = $event->getRequest()->getQueryParams()['id'] ?? null;
        if ($identifier === null) {
            return;
        }

        $buttons = $event->getButtons();
        foreach ($this->generateButtons((string)$identifier) as $buttonInfo) {
            $button = $this->componentFactory->createLinkButton();
            $button->setIcon($buttonInfo['icon']);
            $button->setTitle($buttonInfo['title']);
            $button->setHref($buttonInfo['url']);
            $buttons[ButtonBar::BUTTON_POSITION_LEFT][1][] = $button;
        }
        $event->setButtons($buttons);
    }

    /**
     * Create button
     */
    protected function createLink(string $title, string $shortTitle, Icon $icon, UriInterface $url, bool $addReturnUrl = true): array
    {
        return [
            'title' => $title,
            'icon' => $icon,
            'url' => $url . ($addReturnUrl ? '&returnUrl=' . rawurlencode((string)$_SERVER['REQUEST_URI']) : ''),
        ];
    }
}
