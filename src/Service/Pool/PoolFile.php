<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\Pool;

class PoolFile extends PoolItem
{
    public function __construct(string $filename, string $fullpath)
    {
        parent::__construct($filename, $fullpath);
    }

    public function getType(): string
    {
        return PoolItem::TYPE_FILE;
    }
}
