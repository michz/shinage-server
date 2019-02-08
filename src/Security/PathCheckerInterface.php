<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

interface PathCheckerInterface
{
    /**
     * @param array|string[] $basePaths
     */
    public function inAllowedBasePaths(string $target, array $basePaths): bool;
}
