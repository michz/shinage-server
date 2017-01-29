<?php

namespace AppBundle\Service\Pool;

abstract class PoolItem
{
    const TYPE_DIRECTORY    = 'dir';
    const TYPE_FILE         = 'file';

    protected $name         = '';
    protected $fullpath     = '';

    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->fullpath = $path;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullPath()
    {
        return $this->fullpath;
    }

    abstract public function getType();
}
