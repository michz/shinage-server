<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\Presentation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class PresentationsRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Presentation[]
     */
    public function getPresentationsForsUser(User $user): array
    {
        $organizations = $user->getOrganizations();
        $users = [$user];
        foreach ($organizations as $organization) {
            $users[] = $organization;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('presentation')
            ->from(Presentation::class, 'presentation')
            ->where($queryBuilder->expr()->in('presentation.owner', ':users'))
            ->setParameter(':users', $users);

        return $queryBuilder->getQuery()->execute();
    }
}
