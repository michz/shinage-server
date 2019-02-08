<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Authentication\Provider;

use App\Security\Authentication\Token\ApiUserToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiTokenProvider implements AuthenticationProviderInterface
{
    /** @var UserProviderInterface */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $authenticatedToken = new ApiUserToken($user->getRoles());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiUserToken;
    }
}
