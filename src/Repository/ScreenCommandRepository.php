<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\Screen;
use App\Entity\ScreenCommand;
use Doctrine\ORM\EntityManagerInterface;

class ScreenCommandRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getOldestCommandForScreenIfAny(Screen $screen): ?ScreenCommand
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from(ScreenCommand::class, 's')
            ->where($queryBuilder->expr()->eq('s.screen', ':screen'))
            ->andWhere($queryBuilder->expr()->isNull('s.fetched'))
            ->orderBy('s.created', 'ASC')
            ->setMaxResults(1)
            ->setParameter('screen', $screen);

        $commands = $queryBuilder->getQuery()->execute();
        if (empty($commands)) {
            return null;
        }

        return $commands[0];
    }
}
