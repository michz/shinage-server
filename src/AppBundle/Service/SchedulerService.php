<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScheduledPresentation;
use AppBundle\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SchedulerService
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getCurrentPresentation(Screen $screen, bool $fallbackToDefault = true): ?Presentation
    {
        $em = $this->em;

        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start <= :now AND p.scheduled_end >= :now)
                        ) AND 
                        p.screen = :screen
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('now', date('Y-m-d H:i:s'))
            ->setParameter('screen', $screen);

        $results = $query->getResult();

        if (count($results) > 0) {
            /** @var ScheduledPresentation $p */
            $p = $results[0];
            return $p->getPresentation();
        }

        // no presentation scheduled, so return default
        if ($fallbackToDefault) {
            return $screen->getDefaultPresentation();
        }

        // don't fallback to default, so return null
        return null;
    }

    /**
     * Gets the current Presentation of the given Screen and writes it to the Entity.
     */
    public function updateScreen(Screen $screen, bool $fallbackDefault = true): void
    {
        $screen->setCurrentPresentation($this->getCurrentPresentation($screen, $fallbackDefault));
    }

    /**
     * Delete all scheduled entries of the given presentation.
     */
    public function deleteAllScheduledPresentationsForPresentation(Presentation $presentation): void
    {
        $q = $this->em->createQuery(
            'delete from AppBundle:ScheduledPresentation p where p.presentation = :presentation'
        );
        $q->setParameter('presentation', $presentation);
        $q->execute();
        $this->em->flush();
    }
}
