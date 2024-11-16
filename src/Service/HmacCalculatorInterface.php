<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Stringable;

interface HmacCalculatorInterface
{
    /**
     * @param array<string, string|Stringable> $data
     */
    public function calculate(array $data): string;

    /**
     * @param array<string, string|Stringable> $data
     */
    public function verify(array $data, string $hmac): bool;
}
