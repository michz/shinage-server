<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoggedInUserRepository implements LoggedInUserRepositoryInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
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
                $this->logger->error('Tried to log in with disabled user: ' . $user->getId());
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
