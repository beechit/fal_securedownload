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

use BeechIt\FalSecuredownload\Service\LeafStateService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FileTreeStateController
 */
class FileTreeStateController
{
    /**
     * @var object|Context
     */
    protected $context;

    /**
     * @var LeafStateService|object
     */
    protected $leafStateService;

    public function __construct(Context $context = null, LeafStateService $leafStateService = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->leafStateService = $leafStateService ?? GeneralUtility::makeInstance(LeafStateService::class);
    }

    /**
     * Saves the current Leaf state of a user
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function saveLeafState(ServerRequestInterface $request): ResponseInterface
    {
        $folder = $request->getParsedBody()['folder'] ?? $request->getQueryParams()['folder'];
        if (empty($folder)) {
            return (new Response())->withStatus(404);
        }
        $open = (bool)($request->getParsedBody()['open'] ?? $request->getQueryParams()['open']);
        $userAspect = $this->context->getAspect('beechit.user');
        $this->leafStateService->saveLeafStateForUser($userAspect->get('user'), $folder, $open);
        return new JsonResponse([]);
    }
}
