<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class FilePoolUrlBuilder
{
    protected string $basePath = '';

    protected RouterInterface $router;

    private PathConcatenatorInterface $pathConcatenator;

    public function __construct(
        string $basePath,
        RouterInterface $router,
        PathConcatenatorInterface $pathConcatenator
    ) {
        $this->basePath = $basePath;
        $this->router = $router;
        $this->pathConcatenator = $pathConcatenator;
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
