<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Security;

interface PathCheckerInterface
{
    /**
     * @param array|string[] $basePaths
     */
    public function inAllowedBasePaths(string $target, array $basePaths): bool;
}
