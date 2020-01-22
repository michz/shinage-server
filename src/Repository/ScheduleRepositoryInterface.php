<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\ScheduledPresentation;
use App\Entity\Screen;

interface ScheduleRepositoryInterface
{
    public function reset(): ScheduleRepository;

    /**
     * @return array|ScheduledPresentation[]
     */
    public function getResults(): array;

    public function addScreenConstraint(Screen $screen): ScheduleRepository;

    public function addFromConstraint(\DateTime $from): ScheduleRepository;

    public function addUntilConstraint(\DateTime $until): ScheduleRepository;

    /**
     * @param array|int[] $userIds
     */
    public function addUsersByIdsConstraint(array $userIds): ScheduleRepository;
}
