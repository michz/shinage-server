<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class FilePoolUrlBuilder
{
    /** @var string */
    protected $basePath = '';

    /** @var RouterInterface */
    protected $router;

    /**
     * FilePoolUrlBuilder constructor.
     */
    public function __construct(string $basePath, RouterInterface $router)
    {
        $this->basePath = $basePath;
        $this->router = $router;
    }

    public function getAbsolutePath(string $filePath, string $userRoot = ''): string
    {
        $relative = $this->concatPaths($userRoot, $filePath);
        $absolute = realpath($this->concatPaths($this->basePath, $relative));
        if (false === $absolute) {
            throw new FileNotFoundException($relative);
        }
        $absoluteBase = realpath($this->basePath);
        if (0 !== strpos($absolute, $absoluteBase)) {
            throw new AccessDeniedException($relative);
        }
        return $absolute;
    }

    private function concatPaths(string $left, string $right): string
    {
        return rtrim($left, '/\\') . DIRECTORY_SEPARATOR . ltrim($right, '/\\');
    }
}
