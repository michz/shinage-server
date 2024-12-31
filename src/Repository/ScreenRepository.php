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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

readonly class ScreenRepository implements ScreenRepositoryInterface
{
    /** @var EntityRepository<Screen> */
    private EntityRepository $screenRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->screenRepository = $this->entityManager->getRepository(Screen::class);
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

    public function getScreenByConnectCode(string $connectCode): ?Screen
    {
        return $this->screenRepository->findOneBy(['connect_code' => $connectCode]);
    }
}
