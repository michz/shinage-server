<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

class PathConcatenator implements PathConcatenatorInterface
{
    public function concatTwo(string $left, string $right): string
    {
        return rtrim($left, '/\\') . DIRECTORY_SEPARATOR . ltrim($right, '/\\');
    }
}
