<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

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
     *
     * @param string          $basePath
     * @param RouterInterface $router
     */
    public function __construct(string $basePath, RouterInterface $router)
    {
        $this->basePath = $basePath;
        $this->router = $router;
    }

    /**
     * @param string $filePath
     * @param string $userRoot
     *
     * @return string
     */
    public function getAbsolutePath(string $filePath, string $userRoot = ''): string
    {
        $relative = $this->concatPaths($userRoot, $filePath);
        $absolute = realpath($this->concatPaths($this->basePath, $relative));
        if ($absolute === false) {
            throw new FileNotFoundException($relative);
        }
        $absoluteBase = realpath($this->basePath);
        if (0 !== strpos($absolute, $absoluteBase)) {
            throw new AccessDeniedException($relative);
        }
        return $absolute;
    }

    /**
     * @param string $left
     * @param string $right
     *
     * @return string
     */
    private function concatPaths(string $left, string $right): string
    {
        return rtrim($left, '/\\').DIRECTORY_SEPARATOR.ltrim($right, '/\\');
    }
}
