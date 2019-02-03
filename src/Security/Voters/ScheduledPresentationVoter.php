<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Voters;

use App\Entity\ScheduledPresentation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScheduledPresentationVoter extends Voter
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof ScheduledPresentation;
    }

    /**
     * @param ScheduledPresentation $subject
     *
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // If the action is PUT then we also need the rights to the presentation
        if ('put' === $attribute) {
            if (false === $this->authorizationChecker->isGranted('get', $subject->getPresentation())) {
                return false;
            }
        }

        $screen = $subject->getScreen();
        return $this->authorizationChecker->isGranted('schedule', $screen);
    }
}
