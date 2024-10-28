<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Provider;

interface TimezoneProviderInterface
{
    /**
     * @return array<string, string>
     */
    public function getAvailableTimezones(): array;
}
