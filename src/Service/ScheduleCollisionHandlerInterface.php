<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\ScheduledPresentation;

interface ScheduleCollisionHandlerInterface
{
    public function handleCollisions(ScheduledPresentation $scheduledPresentation): void;
}
