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

    protected string $name = '';

    protected string $fullpath = '';

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->fullpath = $path;
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
