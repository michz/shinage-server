<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

readonly class UserRepository implements UserRepositoryInterface
{
    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->userRepository = $this->entityManager->getRepository(User::class);
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

    public function findOneByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
