<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\Pool;

abstract class PoolItem
{
    const TYPE_DIRECTORY = 'dir';
    const TYPE_FILE = 'file';

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $fullpath = '';

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
