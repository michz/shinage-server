<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Entity\ScreenAssociation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\ORM\QueryBuilder */
    private $queryBuilder;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;

        $this->reset();
    }

    public function reset(): self
    {
        $this->queryBuilder = $this->entityManager->createQueryBuilder();
        $this->queryBuilder
            ->select('sp')
            ->from(ScheduledPresentation::class, 'sp');

        return $this;
    }

    /**
     * @return array|ScheduledPresentation[]
     */
    public function getResults(): array
    {
        return $this->queryBuilder->getQuery()->execute();
    }

    public function addScreenConstraint(Screen $screen): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->eq('sp.screen', ':screen'))
            ->setParameter('screen', $screen);

        return $this;
    }

    public function addFromConstraint(\DateTime $from): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->gte('sp.scheduled_end', ':start_date'))
            ->setParameter(':start_date', $from->format('Y-m-d H:i:s'));

        return $this;
    }

    public function addUntilConstraint(\DateTime $until): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->lte('sp.scheduled_start', ':end_date'))
            ->setParameter('end_date', $until->format('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * @param array|int[] $userIds
     */
    public function addUsersByIdsConstraint(array $userIds): self
    {
        $this->queryBuilder
            ->join(
                Screen::class,
                's',
                Expr\Join::WITH,
                $this->queryBuilder->expr()->eq('sp.screen', 's.guid')
            )
            ->innerJoin(
                ScreenAssociation::class,
                'association',
                Expr\Join::WITH,
                $this->queryBuilder->expr()->eq('association.screen', 's.guid')
            )
            ->andWhere($this->queryBuilder->expr()->in('association.user', $userIds));

        return $this;
    }
}
