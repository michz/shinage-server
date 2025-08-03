<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

readonly class VersionChecker
{
    public function __construct(private string $rootPath)
    {
    }

    /**
     * Returns the current installed version.
     */
    public function getVersion(): string
    {
        $filename = $this->rootPath . '/REVISION';
        if (\file_exists($filename)) {
            return \trim(\file_get_contents($filename));
        }

        return 'dev';
    }
}
