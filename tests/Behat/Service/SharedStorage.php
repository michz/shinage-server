<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Service;

/*
 * This file is inspried by Sylius. Thanks for fantastic open source software!
 * See: https://github.com/Sylius/Sylius/blob/master/src/Sylius/Behat/Service/SharedStorage.php
 */
class SharedStorage implements SharedStorageInterface
{
    /** @var mixed[] */
    private array $clipboard = [];

    private ?string $latestKey = null;

    public function get(string $key): mixed
    {
        if (!isset($this->clipboard[$key])) {
            throw new \InvalidArgumentException(\sprintf('There is no current resource for "%s"!', $key));
        }

        return $this->clipboard[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->clipboard[$key]);
    }

    public function set(string $key, mixed $resource): void
    {
        $this->clipboard[$key] = $resource;
        $this->latestKey = $key;
    }

    public function getLatestResource(): mixed
    {
        if (!isset($this->clipboard[$this->latestKey])) {
            throw new \InvalidArgumentException(\sprintf('There is no "%s" latest resource!', $this->latestKey));
        }

        return $this->clipboard[$this->latestKey];
    }

    /**
     * {@inheritdoc}
     */
    public function setClipboard(array $clipboard): void
    {
        $this->clipboard = $clipboard;
    }
}
