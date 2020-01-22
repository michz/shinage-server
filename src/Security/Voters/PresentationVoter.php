<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Voters;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\User;
use App\Security\VolatileScreenUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PresentationVoter extends Voter
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Presentation;
    }

    /**
     * @param Presentation $subject
     *
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if ($user instanceof VolatileScreenUser) {
            $screen = $user->getScreen();
            // check if presentation is scheduled for this screen

            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('count(sp.id)')
                ->from(ScheduledPresentation::class, 'sp')
                ->where($queryBuilder->expr()->eq('sp.screen', ':screen'))
                ->andWhere($queryBuilder->expr()->eq('sp.presentation', ':presentation'))
                ->setParameter('screen', $screen)
                ->setParameter('presentation', $subject);

            $count = (int) $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
            return $count > 0;
        }

        $owner = $subject->getOwner();
        if ($owner === $user) {
            return true;
        }

        /** @var User $user */
        if ($user->getOrganizations()->contains($owner)) {
            return true;
        }

        return false;
    }
}
