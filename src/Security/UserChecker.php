<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use App\Entity\User;
use App\UserType;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (false === ($user instanceof User)) {
            throw new UnsupportedUserException();
        }

        if (false === $user->isEnabled() && null !== $user->getConfirmationToken()) {
            // User has not confirmed the email address yet
            throw new CustomUserMessageAccountStatusException('Email address not verified.');
        }

        if (UserType::USER_TYPE_USER !== $user->getUserType()) {
            // Should not happen, but just in case an organization has a password set accidentally.
            throw new CustomUserMessageAccountStatusException('Organizations can not login.');
        }

        if (false === $user->isEnabled()) {
            throw new DisabledException();
        }
    }
}
