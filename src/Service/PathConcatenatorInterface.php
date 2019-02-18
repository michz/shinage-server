<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

interface PathConcatenatorInterface
{
    public function concatTwo(string $left, string $right): string;
}
