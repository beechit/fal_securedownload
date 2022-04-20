<?php

declare(strict_types = 1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace BeechIt\FalSecuredownload\Context;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

/**
 * The aspect contains a user.
 * Can be used for frontend and backend users.
 *
 * Allowed properties:
 * - user
 */
class UserAspect implements AspectInterface
{
    /**
     * @var AbstractUserAuthentication
     */
    protected $user;

    /**
     * @param AbstractUserAuthentication|null $user
     * @param array|null $alternativeGroups
     */
    public function __construct(AbstractUserAuthentication $user = null)
    {
        $this->user = $user ?? $this->createPseudoUser();
    }

    private function createPseudoUser(): object
    {
        $user = new \stdClass();
        $user->user = [];
        return $user;
    }

    /**
     * Fetch common information about the user
     *
     * @param string $name
     * @return int|bool|string|array
     * @throws AspectPropertyNotFoundException
     */
    public function get(string $name)
    {
        switch ($name) {
            case 'user':
                return $this->user;
        }
        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1597220199);
    }
}
