<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoggedInUserRepository implements LoggedInUserRepositoryInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function getLoggedInUserOrDenyAccess(): User
    {
        try {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \RuntimeException('Got no token.');
            }

            /** @var User|null $user */
            $user = $token->getUser();
            if (false === $user instanceof User) {
                throw new \RuntimeException('No user in session.');
            }

            if (false === $user->isEnabled()) {
                throw new \RuntimeException('User is deactivated.');
            }

            return $user;
        } catch (\Throwable $throwable) {
            throw new AccessDeniedException(
                'Could not find a User object in current session. Not logged in?',
                $throwable
            );
        }
    }
}
