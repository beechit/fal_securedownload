<?php

namespace BeechIt\FalSecuredownload\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Controller\FileDumpController;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileTreeStateMiddleware implements MiddlewareInterface
{
    /**
     * Dispatches the request to the corresponding eID class or eID script
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $eID = $request->getParsedBody()['treeState'] ?? $request->getQueryParams()['treeState'] ?? null;

        if ($eID === null || $eID !== 'FalSecuredownloadFileTreeState') {
            return $handler->handle($request);
        }

        $target = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$eID] ?? null;
        if (empty($target)) {
            return (new Response())->withStatus(404, 'eID not registered');
        }

        $request = $request->withAttribute('target', $target);
        return $handler->handle($request);
    }

}