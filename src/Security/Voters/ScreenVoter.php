<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Voters;

use App\Entity\Screen;
use App\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScreenVoter extends Voter
{
    /** @var ScreenAssociation */
    private $screenAssociation;

    public function __construct(ScreenAssociation $screenAssociation)
    {
        $this->screenAssociation = $screenAssociation;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Screen;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->screenAssociation->isUserAllowedTo($subject, $token->getUser(), $attribute);
    }
}
