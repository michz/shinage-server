<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 *
 * This file is inspried by Sylius. Thanks for fantastic open source software!
 * See: https://github.com/Sylius/Sylius/blob/master/src/Sylius/Behat/Service/SharedStorage.php
 */

namespace shinage\server\behat\Transform;

use Behat\Behat\Context\Context;
use shinage\server\behat\Helper\StringInflector;
use shinage\server\behat\Service\SharedStorageInterface;

class SharedStorageContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @param SharedStorageInterface $sharedStorage */
    public function __construct(SharedStorageInterface $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Transform /^(it|its|theirs|them)$/
     */
    public function getLatestResource()
    {
        return $this->sharedStorage->getLatestResource();
    }

    /**
     * @Transform /^(?:this|that|the) ([^"]+)$/
     */
    public function getResource(string $resource)
    {
        return $this->sharedStorage->get(StringInflector::nameToCode($resource));
    }
}
