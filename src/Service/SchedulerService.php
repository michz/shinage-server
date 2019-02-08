<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\Presentation;
use App\Entity\PresentationInterface;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
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

    public function getCurrentPresentation(Screen $screen, bool $fallbackToDefault = true): ?PresentationInterface
    {
        $em = $this->em;

        $query = $em->createQuery(
            'SELECT p
                    FROM App:ScheduledPresentation p
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
        $defaultPresentation = $screen->getDefaultPresentation();
        if ($fallbackToDefault && null !== $defaultPresentation) {
            return $defaultPresentation;
        }

        // last option: splash screen
        $splash = new Presentation();
        $splash->setId(0);
        $splash->setType('splash');
        $splash->setSettings('{}');
        $splash->setLastModified(new \DateTime(gmdate('Y-m-d 00:00:00')));
        return $splash;
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
    public function deleteAllScheduledPresentationsForPresentation(PresentationInterface $presentation): void
    {
        $q = $this->em->createQuery(
            'delete from App:ScheduledPresentation p where p.presentation = :presentation'
        );
        $q->setParameter('presentation', $presentation);
        $q->execute();
        $this->em->flush();
    }
}
