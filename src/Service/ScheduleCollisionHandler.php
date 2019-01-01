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

    /** @var Query */
    private $selectOverlappingAtStartQuery;

    /** @var Query */
    private $selectOverlappingAtEndQuery;

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

        $selectOverlappingAtStartQueryBuilder = $this->entityManager->createQueryBuilder();
        $this->selectOverlappingAtStartQuery =
            $selectOverlappingAtStartQueryBuilder
                ->select('scheduledPresentation')
                ->from(ScheduledPresentation::class, 'scheduledPresentation')
                ->where($selectOverlappingAtStartQueryBuilder->expr()->gt('scheduledPresentation.scheduled_start', ':current_start'))
                ->andWhere($selectOverlappingAtStartQueryBuilder->expr()->lt('scheduledPresentation.scheduled_start', ':current_end'))
                ->andWhere($selectOverlappingAtStartQueryBuilder->expr()->gte('scheduledPresentation.scheduled_end', ':current_end'))
                ->andWhere($selectOverlappingAtStartQueryBuilder->expr()->eq('scheduledPresentation.screen', ':screen'))
                ->andWhere($selectOverlappingAtStartQueryBuilder->expr()->neq('scheduledPresentation.id', ':id'))
                ->orderBy('scheduledPresentation.scheduled_start', 'ASC')
                ->getQuery();

        $selectOverlappingAtEndQueryBuilder = $this->entityManager->createQueryBuilder();
        $this->selectOverlappingAtEndQuery =
            $selectOverlappingAtEndQueryBuilder
                ->select('scheduledPresentation')
                ->from(ScheduledPresentation::class, 'scheduledPresentation')
                ->where($selectOverlappingAtEndQueryBuilder->expr()->lt('scheduledPresentation.scheduled_start', ':current_start'))
                ->andWhere($selectOverlappingAtEndQueryBuilder->expr()->lt('scheduledPresentation.scheduled_end', ':current_end'))
                ->andWhere($selectOverlappingAtEndQueryBuilder->expr()->gte('scheduledPresentation.scheduled_end', ':current_start'))
                ->andWhere($selectOverlappingAtEndQueryBuilder->expr()->eq('scheduledPresentation.screen', ':screen'))
                ->andWhere($selectOverlappingAtEndQueryBuilder->expr()->neq('scheduledPresentation.id', ':id'))
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
            $this->entityManager->flush();
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
            $this->entityManager->persist($new_o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation overlaps same/other schedule on same screen at beginning
        $overlaps = $this->selectOverlappingAtStartQuery
            ->execute([
                'current_start' => $start,
                'current_end'   => $end,
                'id'            => $s->getId(),
                'screen'        => $s->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            if ($o->getPresentation() === $s->getPresentation()) {
                // The same presentation is scheduled twice overlapping, merge.
                $o->setScheduledStart($s->getScheduledStart());
                $this->entityManager->remove($s);
                $this->entityManager->flush();
                return;
            } else {
                $o->setScheduledStart($s->getScheduledEnd());
            }
        }

        $this->entityManager->flush();

        // check if scheduled presentation overlaps same/other schedule on same screen at end
        $overlaps = $this->selectOverlappingAtEndQuery
            ->execute([
                'current_start' => $start,
                'current_end'   => $end,
                'id'            => $s->getId(),
                'screen'        => $s->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            if ($o->getPresentation() === $s->getPresentation()) {
                // The same presentation is scheduled twice overlapping, merge.
                $o->setScheduledEnd($s->getScheduledEnd());
                $this->entityManager->remove($s);
                $this->entityManager->flush();
                return;
            } else {
                $o->setScheduledEnd($s->getScheduledStart());
            }
        }

        $this->entityManager->flush();
    }
}
