<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Voters;

use App\Entity\Screen;
use App\Entity\User;
use App\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Screen>
 */
class ScreenVoter extends Voter
{
    private ScreenAssociation $screenAssociation;

    public function __construct(ScreenAssociation $screenAssociation)
    {
        $this->screenAssociation = $screenAssociation;
    }

    /**
     * @param Screen $subject
     *
     * {@inheritdoc}
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Screen;
    }

    /**
     * @param Screen $subject
     *
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (false === $user instanceof User) {
            return false;
        }

        return $this->screenAssociation->isUserAllowedTo($subject, $user, $attribute);
    }
}
