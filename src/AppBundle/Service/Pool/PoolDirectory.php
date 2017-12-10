<?php

namespace AppBundle\Service\Pool;

class PoolDirectory extends PoolItem
{
    /** @var PoolFile[] */
    protected $contents = [];

    public function __construct($filename, $fullpath)
    {
        parent::__construct($filename, $fullpath);
    }

    public function &getContents()
    {
        return $this->contents;
    }

    public function getType()
    {
        return PoolItem::TYPE_DIRECTORY;
    }
}
