<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\Pool;

interface VirtualPathResolverInterface
{
    public function replaceVirtualBasePath(string $path): string;
}
