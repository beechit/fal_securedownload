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

/**
 * Class SecureDownloadMiddleware
 * @package BeechIt\FalSecuredownload\Middleware
 * Copy of EidMiddleWare from the core, dedicated to call file dumpController
 * Due though late initiating of frontendUserAuthenticate middleware.
 */
class SecureDownloadMiddleware implements MiddlewareInterface
{

    /**
     * @var FileDumpController
     */
    protected $fileDumpController;

    public function __construct()
    {
        $this->fileDumpController = GeneralUtility::makeInstance(FileDumpController::class);
    }

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
        $eID = $request->getParsedBody()['secureDownload'] ?? $request->getQueryParams()['secureDownload'] ?? null;

        if ($eID === null || $eID !== 'dumpFile') {
            return $handler->handle($request);
        }

        // Remove any output produced until now
        ob_clean();

        $target = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$eID] ?? null;
        if (empty($target)) {
            return (new Response())->withStatus(404, 'eID not registered');
        }

        $request = $request->withAttribute('target', $target);
        $this->fileDumpController->dumpAction($request);
        return $handler->handle($request);
    }

}