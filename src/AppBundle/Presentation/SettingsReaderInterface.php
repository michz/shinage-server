<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Presentation;

interface SettingsReaderInterface
{
    /**
     * @return object|mixed
     */
    public function get(string $serializedSettings);
}
