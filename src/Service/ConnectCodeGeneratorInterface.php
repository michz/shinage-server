<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

interface ConnectCodeGeneratorInterface
{
    public function generateUniqueConnectcode(): string;
}
