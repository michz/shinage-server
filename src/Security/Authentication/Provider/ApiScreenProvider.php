<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Authentication\Provider;

use App\Security\Authentication\Token\ApiScreenToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApiScreenProvider implements AuthenticationProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $token->getUser();

        $authenticatedToken = new ApiScreenToken();
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiScreenToken;
    }
}
