<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 31.12.17
 * Time: 12:14
 */

namespace AppBundle\Security\Voters;

use AppBundle\Entity\Screen;
use AppBundle\Entity\User;
use AppBundle\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScreenVoter extends Voter
{
    /** @var ScreenAssociation */
    private $screenAssociation;

    /**
     * ScreenVoter constructor.
     * @param ScreenAssociation $screenAssociation
     */
    public function __construct(ScreenAssociation $screenAssociation)
    {
        $this->screenAssociation = $screenAssociation;
    }

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        return ($subject instanceof Screen);
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        return $this->screenAssociation->isUserAllowedTo($subject, $user, $attribute);
    }
}
