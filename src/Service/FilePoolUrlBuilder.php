<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

readonly class FilePoolUrlBuilder
{
    public function __construct(
        private string $basePath,
        private PathConcatenatorInterface $pathConcatenator,
    ) {
    }

    public function getAbsolutePath(string $filePath, string $userRoot = ''): string
    {
        $relative = $this->pathConcatenator->concatTwo($userRoot, $filePath);
        $absolute = \realpath($this->pathConcatenator->concatTwo($this->basePath, $relative));
        if (false === $absolute) {
            throw new FileNotFoundException($relative);
        }

        $absoluteBase = \realpath($this->basePath);
        if (0 !== \strpos($absolute, $absoluteBase)) {
            throw new AccessDeniedException($relative);
        }

        return $absolute;
    }
}
