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
use Psr\Clock\ClockInterface;

readonly class SchedulerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    public function getCurrentPresentation(Screen $screen, bool $fallbackToDefault = true): ?PresentationInterface
    {
        // Times in database are always interpreted as UTC.
        $utc = new \DateTimeZone('UTC');
        $now = \DateTime::createFromImmutable($this->clock->now());
        $now->setTimezone($utc);
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from(ScheduledPresentation::class, 'p')
            ->where('p.screen = :screen')
            ->andWhere('p.scheduled_start <= :now')
            ->andWhere('p.scheduled_end >= :now')
            ->setParameter('now', $now->format('Y-m-d H:i:s'))
            ->setParameter('screen', $screen);

        $results = $queryBuilder->getQuery()->getResult();

        if (\count($results) > 0) {
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
        $splash->setLastModified(new \DateTime(\gmdate('Y-m-d 00:00:00')));
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
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->delete(ScheduledPresentation::class, 'p')
            ->where('p.presentation = :presentation')
            ->setParameter('presentation', $presentation);

        $queryBuilder->getQuery()->execute();
        $this->entityManager->flush();
    }
}
