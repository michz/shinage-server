<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Voters;

use App\Entity\Presentation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Presentation>
 */
class PresentationVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Presentation;
    }

    /**
     * @param Presentation $subject
     *
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $owner = $subject->getOwner();
        /** @var User $user */
        $user = $token->getUser();

        if ($owner === $user) {
            return true;
        }

        if ($user->getOrganizations()->contains($owner)) {
            return true;
        }

        return false;
    }
}
