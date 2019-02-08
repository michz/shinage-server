<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Service;

/*
 * This file is inspried by Sylius. Thanks for fantastic open source software!
 * See: https://github.com/Sylius/Sylius/blob/master/src/Sylius/Behat/Service/SharedStorage.php
 */
class SharedStorage implements SharedStorageInterface
{
    /** @var array|mixed[] */
    private $clipboard = [];

    /** @var string|null */
    private $latestKey;

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        if (!isset($this->clipboard[$key])) {
            throw new \InvalidArgumentException(sprintf('There is no current resource for "%s"!', $key));
        }

        return $this->clipboard[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return isset($this->clipboard[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $resource): void
    {
        $this->clipboard[$key] = $resource;
        $this->latestKey = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestResource()
    {
        if (!isset($this->clipboard[$this->latestKey])) {
            throw new \InvalidArgumentException(sprintf('There is no "%s" latest resource!', $this->latestKey));
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
