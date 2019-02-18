<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Service;

interface SharedStorageInterface
{
    /**
     * @return mixed
     */
    public function get(string $key);

    /**
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param mixed $resource
     */
    public function set(string $key, $resource): void;

    /**
     * @return mixed
     */
    public function getLatestResource();

    /**
     * @param array|mixed[] $clipboard
     */
    public function setClipboard(array $clipboard): void;
}
