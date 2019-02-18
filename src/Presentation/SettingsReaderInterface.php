<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

interface SettingsReaderInterface
{
    /**
     * @return object|mixed
     */
    public function get(string $serializedSettings);
}
