<?php

namespace AppBundle\Service\Pool;

class PoolFile extends PoolItem
{

    public function __construct($filename, $fullpath)
    {
        parent::__construct($filename, $fullpath);
    }

    public function getType()
    {
        return PoolItem::TYPE_FILE;
    }
}
