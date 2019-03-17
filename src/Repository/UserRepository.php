<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository implements UserRepositoryInterface
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
    public function findOrganizationsByMailHost(string $host): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('user')
            ->from(User::class, 'user')
            ->where($queryBuilder->expr()->eq('user.userType', ':userType'))
            ->andWhere($queryBuilder->expr()->like('user.emailCanonical', ':mailHost'));

        return $queryBuilder->getQuery()->execute([
            ':userType' => 'organization',
            ':mailHost' => '%@' . $host,
        ]);
    }
}
