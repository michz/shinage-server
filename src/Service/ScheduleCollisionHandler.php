<?php
declare(strict_types=1);

/*
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
        $this->createSelectSameEnclosingQuery();
        $this->createSelectEnclosedQuery();
        $this->createSelectOtherEnclosingQuery();
        $this->createOverlappingAtStartQuery();
        $this->createOverlappingAtEndQuery();
    }

    public function handleCollisions(ScheduledPresentation $scheduledPresentation): void
    {
        $removed = $this->handleSameEnclosing($scheduledPresentation);
        if ($removed) {
            return;
        }

        $this->handleEnclosed($scheduledPresentation);

        $this->handleEntirelyEnclosing($scheduledPresentation);

        $removed = $this->handleOverlapsAtStart($scheduledPresentation);
        if ($removed) {
            return;
        }

        $removed = $this->handleOverlapsAtEnd($scheduledPresentation);
        if ($removed) {
            return;
        }
    }

    private function createSelectSameEnclosingQuery(): void
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
    }

    private function createSelectEnclosedQuery(): void
    {
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
    }

    private function createSelectOtherEnclosingQuery(): void
    {
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

    private function createOverlappingAtStartQuery(): void
    {
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
    }

    private function createOverlappingAtEndQuery(): void
    {
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

    /**
     * First check if new presentation is entirely enclosed by the same presentation => remove the new one.
     */
    private function handleSameEnclosing(ScheduledPresentation $scheduledPresentation): bool
    {
        $overlaps = $this->selectSameEnclosingQuery
            ->execute([
                'current_start' => $scheduledPresentation->getScheduledStart(),
                'current_end' => $scheduledPresentation->getScheduledEnd(),
                'id' => $scheduledPresentation->getId(),
                'screen' => $scheduledPresentation->getScreen(),
                'presentation' => $scheduledPresentation->getPresentation(),
            ]);

        if (false === empty($overlaps)) {
            $this->entityManager->remove($scheduledPresentation);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * Check if scheduled presentation encloses same/other schedule on same screen entirely.
     */
    private function handleEnclosed(ScheduledPresentation $scheduledPresentation): void
    {
        $overlaps = $this->selectEnclosedQuery
            ->execute([
                'current_start' => $scheduledPresentation->getScheduledStart(),
                'current_end' => $scheduledPresentation->getScheduledEnd(),
                'id' => $scheduledPresentation->getId(),
                'screen' => $scheduledPresentation->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            // remove all that are fully enclosed
            $this->entityManager->remove($o);
        }

        $this->entityManager->flush();
    }

    /**
     * Check if scheduled presentation is entirely enclosed by same/other schedule on same screen.
     */
    private function handleEntirelyEnclosing(ScheduledPresentation $scheduledPresentation): void
    {
        $overlaps = $this->selectOtherEnclosingQuery
            ->execute([
                'current_start' => $scheduledPresentation->getScheduledStart(),
                'current_end' => $scheduledPresentation->getScheduledEnd(),
                'id' => $scheduledPresentation->getId(),
                'screen' => $scheduledPresentation->getScreen(),
            ]);

        /** @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            $new_o = new ScheduledPresentation();
            $new_o->setScreen($o->getScreen());
            $new_o->setPresentation($o->getPresentation());

            // new scheduled item at end
            $new_o->setScheduledStart($scheduledPresentation->getScheduledEnd());
            $new_o->setScheduledEnd($o->getScheduledEnd());

            // old scheduled item at start
            $o->setScheduledEnd($scheduledPresentation->getScheduledStart());
            $this->entityManager->persist($new_o);
        }

        $this->entityManager->flush();
    }

    /**
     * Check if scheduled presentation overlaps same/other schedule on same screen at beginning.
     */
    private function handleOverlapsAtStart(ScheduledPresentation $scheduledPresentation): bool
    {
        $overlaps = $this->selectOverlappingAtStartQuery
            ->execute([
                'current_start' => $scheduledPresentation->getScheduledStart(),
                'current_end'   => $scheduledPresentation->getScheduledEnd(),
                'id'            => $scheduledPresentation->getId(),
                'screen'        => $scheduledPresentation->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            if ($o->getPresentation() === $scheduledPresentation->getPresentation()) {
                // The same presentation is scheduled twice overlapping, merge.
                $o->setScheduledStart($scheduledPresentation->getScheduledStart());
                $this->entityManager->remove($scheduledPresentation);
                $this->entityManager->flush();
                return true;
            } else {
                $o->setScheduledStart($scheduledPresentation->getScheduledEnd());
            }
        }

        $this->entityManager->flush();
        return false;
    }

    /**
     * Check if scheduled presentation overlaps same/other schedule on same screen at end.
     */
    private function handleOverlapsAtEnd(ScheduledPresentation $scheduledPresentation): bool
    {
        $overlaps = $this->selectOverlappingAtEndQuery
            ->execute([
                'current_start' => $scheduledPresentation->getScheduledStart(),
                'current_end' => $scheduledPresentation->getScheduledEnd(),
                'id' => $scheduledPresentation->getId(),
                'screen' => $scheduledPresentation->getScreen(),
            ]);

        /* @var ScheduledPresentation $o */
        foreach ($overlaps as $o) {
            if ($o->getPresentation() === $scheduledPresentation->getPresentation()) {
                // The same presentation is scheduled twice overlapping, merge.
                $o->setScheduledEnd($scheduledPresentation->getScheduledEnd());
                $this->entityManager->remove($scheduledPresentation);
                $this->entityManager->flush();
                return true;
            } else {
                $o->setScheduledEnd($scheduledPresentation->getScheduledStart());
            }
        }

        $this->entityManager->flush();
        return false;
    }
}
