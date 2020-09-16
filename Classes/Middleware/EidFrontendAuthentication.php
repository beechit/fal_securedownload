<?php
namespace BeechIt\FalSecuredownload\Middleware;

use BeechIt\FalSecuredownload\Context\UserAspect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 *
 */
class EidFrontendAuthentication implements MiddlewareInterface
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
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
        $eID = $request->getParsedBody()['eID'] ?? $request->getQueryParams()['eID'] ?? null;

        if ($eID === null || !in_array($eID, ['dumpFile', 'FalSecuredownloadFileTreeState'])) {
            return $handler->handle($request);
        }
        $frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);

        // List of page IDs where to look for frontend user records
        $pid = $request->getParsedBody()['pid'] ?? $request->getQueryParams()['pid'] ?? 0;
        if ($pid) {
            $frontendUser->checkPid_value = implode(',', GeneralUtility::intExplode(',', $pid));
        }

        // Authenticate now
        $frontendUser->start();
        $frontendUser->unpack_uc();

        // Register the frontend user as aspect and within the session
        $this->setFrontendUserAspect($frontendUser);
        $response = $handler->handle($request);
        return $response;
    }

    /**
     * Register the frontend user as aspect
     *
     * @param AbstractUserAuthentication $user
     */
    protected function setFrontendUserAspect(AbstractUserAuthentication $user)
    {
        $this->context->setAspect('beechit.user', GeneralUtility::makeInstance(UserAspect::class, $user));
    }
}
