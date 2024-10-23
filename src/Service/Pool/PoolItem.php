<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\Pool;

abstract class PoolItem
{
    public const TYPE_DIRECTORY = 'dir';
    public const TYPE_FILE = 'file';

    public function __construct(
        protected readonly string $name,
        protected readonly string $fullpath,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullPath(): string
    {
        return $this->fullpath;
    }

    abstract public function getType(): string;
}
