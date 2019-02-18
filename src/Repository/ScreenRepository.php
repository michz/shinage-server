<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\Screen;
use App\Entity\ScreenAssociation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;

class ScreenRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @return Screen[]
     */
    public function getScreensForUser(User $user): array
    {
        $organizations = $user->getOrganizations();
        $users = [$user];
        foreach ($organizations as $organization) {
            $users[] = $organization;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('screen')
            ->from(ScreenAssociation::class, 'assoc')
            ->join(Screen::class, 'screen', Expr\Join::WITH, $queryBuilder->expr()->eq('assoc.screen', 'screen.guid'))
            ->where($queryBuilder->expr()->in('assoc.user', ':users'))
            ->setParameter(':users', $users);

        return $queryBuilder->getQuery()->execute();
    }
}
