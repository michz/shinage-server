<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Provider;

use DateTimeZone;

readonly class TimezoneProvider implements TimezoneProviderInterface
{
    public function __construct(
        private string $timezoneFilter,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableTimezones(): array
    {
        $identifiers = DateTimeZone::listIdentifiers();

        $timezones = [];
        foreach ($identifiers as $identifier) {
            if (\preg_match($this->timezoneFilter, $identifier) < 1) {
                continue;
            }

            $timezones[$identifier] = $identifier;
        }

        return $timezones;
    }
}
