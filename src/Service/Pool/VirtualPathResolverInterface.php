<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\Pool;

interface VirtualPathResolverInterface
{
    public function replaceVirtualBasePath(string $path): string;
}
