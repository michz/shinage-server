<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Service;

interface SharedStorageInterface
{
    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $resource): void;

    public function getLatestResource(): mixed;

    /**
     * @param mixed[] $clipboard
     */
    public function setClipboard(array $clipboard): void;
}
