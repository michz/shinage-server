<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\ScheduledPresentation;
use Doctrine\ORM\EntityManagerInterface;

class ScheduleCollisionHandler implements ScheduleCollisionHandlerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function handleCollisions(ScheduledPresentation $s): void
    {
        $start = $s->getScheduledStart();
        $end = $s->getScheduledEnd();

        // check if scheduled presentation encloses same/other schedule on same screen entirely
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start  >= :current_start AND p.scheduled_end <= :current_end)
                        ) AND 
                        p.screen = :screen
                        AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            // remove all that are fully enclosed
            $this->entityManager->remove($o);
        }

        $this->entityManager->flush();

        // check if scheduled presentation is entirely enclosed by same/other schedule on same screen
        $query = $this->entityManager->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start < :current_start AND p.scheduled_end > :current_end)
                        ) AND 
                        p.screen = :screen
                        AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /** @var ScheduledPresentation $o */
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
