<?php

declare(strict_types=1);

namespace BeechIt\FalSecuredownload\Middleware;

use BeechIt\FalSecuredownload\Context\UserAspect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\Mfa\MfaRequiredException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendBackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class EidFrontendAuthentication implements MiddlewareInterface
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Dispatches the request to the corresponding eID class or eID script
     *
     * @throws MfaRequiredException
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
        $frontendUser->start($request);
        $this->unpackUc($frontendUser);

        // Register the frontend user as aspect and within the session
        $this->setFrontendUserAspect($frontendUser);

        $backendUserObject = GeneralUtility::makeInstance(FrontendBackendUserAuthentication::class);
        $backendUserObject->start($request);
        $this->unpackUc($backendUserObject);
        if (!empty($backendUserObject->user['uid'])) {
            $backendUserObject->fetchGroupData();
        }
        $this->setBackendUserAspect($backendUserObject);

        return $handler->handle($request);
    }

    /**
     * Register the frontend user as aspect
     */
    protected function setFrontendUserAspect(AbstractUserAuthentication $user): void
    {
        $this->context->setAspect('beechit.user', GeneralUtility::makeInstance(UserAspect::class, $user));
    }

    /**
     * Register the backend user as aspect
     */
    protected function setBackendUserAspect(AbstractUserAuthentication $user): void
    {
        $this->context->setAspect('beechit.beuser', GeneralUtility::makeInstance(UserAspect::class, $user));
        $GLOBALS['BE_USER'] = $user;
    }

    protected function unpackUc(AbstractUserAuthentication $user): void
    {
        if (isset($user->user['uc'])) {
            $theUC = unserialize($user->user['uc'], ['allowed_classes' => false]);
            if (is_array($theUC)) {
                $user->uc = $theUC;
            }
        }
    }
}
