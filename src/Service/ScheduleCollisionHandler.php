<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\ScheduledPresentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class ScheduleCollisionHandler implements ScheduleCollisionHandlerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Query */
    private $selectEnclosedQuery;

    /** @var Query */
    private $selectOtherEnclosingQuery;

    /** @var Query */
    private $selectSameEnclosingQuery;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;

        $this->prepareQueries();
    }

    private function prepareQueries(): void
    {
        $selectSameEnclosingQueryBuilder = $this->entityManager->createQueryBuilder();
        $this->selectSameEnclosingQuery =
            $selectSameEnclosingQueryBuilder
                ->select('scheduledPresentation')
                ->from(ScheduledPresentation::class, 'scheduledPresentation')
                ->where($selectSameEnclosingQueryBuilder->expr()->lt('scheduledPresentation.scheduled_start', ':current_start'))
                ->andWhere($selectSameEnclosingQueryBuilder->expr()->gt('scheduledPresentation.scheduled_end', ':current_end'))
                ->andWhere($selectSameEnclosingQueryBuilder->expr()->eq('scheduledPresentation.screen', ':screen'))
                ->andWhere($selectSameEnclosingQueryBuilder->expr()->neq('scheduledPresentation.id', ':id'))
                ->andWhere($selectSameEnclosingQueryBuilder->expr()->eq('scheduledPresentation.presentation', ':presentation'))
                ->orderBy('scheduledPresentation.scheduled_start', 'ASC')
                ->getQuery();

        $selectEnclosedQueryBuilder = $this->entityManager->createQueryBuilder();
        $this->selectEnclosedQuery =
            $selectEnclosedQueryBuilder
                ->select('scheduledPresentation')
                ->from(ScheduledPresentation::class, 'scheduledPresentation')
                ->where($selectEnclosedQueryBuilder->expr()->gte('scheduledPresentation.scheduled_start', ':current_start'))
                ->andWhere($selectEnclosedQueryBuilder->expr()->lte('scheduledPresentation.scheduled_end', ':current_end'))
                ->andWhere($selectEnclosedQueryBuilder->expr()->eq('scheduledPresentation.screen', ':screen'))
                ->andWhere($selectEnclosedQueryBuilder->expr()->neq('scheduledPresentation.id', ':id'))
                ->orderBy('scheduledPresentation.scheduled_start', 'ASC')
                ->getQuery();

        $selectOtherEnclosingQueryBuilder = $this->entityManager->createQueryBuilder();
        $this->selectOtherEnclosingQuery =
            $selectOtherEnclosingQueryBuilder
                ->select('scheduledPresentation')
                ->from(ScheduledPresentation::class, 'scheduledPresentation')
                ->where($selectOtherEnclosingQueryBuilder->expr()->lt('scheduledPresentation.scheduled_start', ':current_start'))
                ->andWhere($selectOtherEnclosingQueryBuilder->expr()->gt('scheduledPresentation.scheduled_end', ':current_end'))
                ->andWhere($selectOtherEnclosingQueryBuilder->expr()->eq('scheduledPresentation.screen', ':screen'))
                ->andWhere($selectOtherEnclosingQueryBuilder->expr()->neq('scheduledPresentation.id', ':id'))
                ->orderBy('scheduledPresentation.scheduled_start', 'ASC')
                ->getQuery();
    }

    public function handleCollisions(ScheduledPresentation $s): void
    {
        $start = $s->getScheduledStart();
        $end = $s->getScheduledEnd();

        // First check if new presentation is entirely enclosed by the same presentation => remove the new one
        $overlaps = $this->selectSameEnclosingQuery
            ->execute([
                'current_start' => $start,
                'current_end'   => $end,
                'id'            => $s->getId(),
                'screen'        => $s->getScreen(),
                'presentation'  => $s->getPresentation(),
            ]);

        if (false === empty($overlaps)) {
            $this->entityManager->remove($s);
            return;
        }

        // check if scheduled presentation encloses same/other schedule on same screen entirely
        $overlaps = $this->selectEnclosedQuery
            ->execute([
                'current_start' => $start,
                'current_end'   => $end,
                'id'            => $s->getId(),
                'screen'        => $s->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            // remove all that are fully enclosed
            $this->entityManager->remove($o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation is entirely enclosed by same/other schedule on same screen
        $overlaps = $this->selectOtherEnclosingQuery
            ->execute([
                'current_start' => $start,
                'current_end'   => $end,
                'id'            => $s->getId(),
                'screen'        => $s->getScreen(),
            ]);

        /** @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            $new_o = new ScheduledPresentation();
            $new_o->setScreen($o->getScreen());
            $new_o->setPresentation($o->getPresentation());

            // new scheduled item at end
            $new_o->setScheduledStart($s->getScheduledEnd());
            $new_o->setScheduledEnd($o->getScheduledEnd());

            // old scheduled item at start
            $o->setScheduledEnd($s->getScheduledStart());
            $this->entityManager->persist($o);
            $this->entityManager->persist($new_o);
        }

        $this->entityManager->flush();

        return;

        // check if scheduled presentation overlaps same/other schedule on same screen
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (p.scheduled_start < :current_start AND 
                        p.scheduled_end >= :current_start AND p.scheduled_end <= :current_end)
                        AND p.screen = :screen AND p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $o->setScheduledEnd($s->getScheduledStart());
            $this->entityManager->persist($o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation overlaps same/other schedule on same screen (second case)
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (p.scheduled_start > :current_start AND 
                        p.scheduled_start <= :current_end AND 
                        p.scheduled_end > :current_end)
                        AND p.screen = :screen AND p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $o->setScheduledStart($s->getScheduledEnd());
            $this->entityManager->persist($o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation overlaps same presentation on same screen at start
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (
                        p.scheduled_start  < :current_start AND
                        p.scheduled_end >= :current_start AND p.scheduled_end <= :current_end
                        ) AND 
                        p.screen = :screen AND 
                        p.presentation = :presentation AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('presentation', $s->getPresentation())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $s->setScheduledStart($o->getScheduledStart());
            $this->entityManager->persist($s);
            $this->entityManager->remove($o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation overlaps same presentation on same screen at end
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (
                        p.scheduled_start >= :current_start AND p.scheduled_start <= :current_end AND
                        p.scheduled_end > :current_end
                        ) AND 
                        p.screen = :screen AND 
                        p.presentation = :presentation AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('presentation', $s->getPresentation())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $s->setScheduledEnd($o->getScheduledEnd());
            $this->entityManager->persist($s);
            $this->entityManager->remove($o);
        }

        $this->entityManager->flush();
    }
}
