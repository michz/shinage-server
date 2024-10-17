<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\User;
use App\Service\Pool\PoolDirectory;
use App\Service\Pool\PoolFile;
use Symfony\Component\Config\Definition\Exception\Exception;

readonly class FilePool
{
    public function __construct(private string $basePath)
    {
        // test if path exists
        if (!\is_dir($basePath)) {
            \mkdir($basePath, 0700, true);
        }

        // if not: try to create
        // test if it exists now; if not: Exception
        if (!\is_dir($basePath)) {
            throw new Exception('FilePool path does not exist and cannot be created.');
        }
    }

    /**
     * @return mixed[]|array
     */
    public function getUserPaths(User $user): array
    {
        $paths = [];
        $paths['me'] = $this->getPathForUser($user);

        $orgas = $user->getOrganizations();
        foreach ($orgas as $o) { /** @var User $o */
            $paths['Org: ' . $o->getName()] = $this->getPathForUser($o);
        }

        return $paths;
    }

    /**
     * @return array|string[]
     */
    public function getPathForUser(User $user): array
    {
        $realBasePath = \realpath($this->basePath);
        if (false === $realBasePath) {
            throw new \RuntimeException('Data pool base path not found.');
        }

        $path = $realBasePath . '/user-' . $user->getId();
        self::createPathIfNeeded($path);
        return [
            'real' => $path,
            'virtual' => $realBasePath . '/' . $user->getEmail(),
        ];
    }

    public function getFileTree(string $base, bool $displayHidden = false): PoolDirectory
    {
        $filename = \substr($base, \strrpos($base, '/') + 1);

        $dir = new PoolDirectory($filename, $base);
        $files = &$dir->getContents();

        if ($handle = \opendir($base)) {
            while (false !== ($entry = \readdir($handle))) {
                // ignore . and ..
                if ('.' == $entry || '..' == $entry) {
                    continue;
                }

                // ignore hidden files
                if (!$displayHidden && '.' == \substr($entry, 0, 1)) {
                    continue;
                }

                if (\is_dir($base . '/' . $entry)) {
                    $files[] = $this->getFileTree($base . '/' . $entry, $displayHidden);
                } elseif (\is_file($base . '/' . $entry)) {
                    $files[] = new PoolFile($entry, $base . '/' . $entry);
                }
            }

            \closedir($handle);
        }

        return $dir;
    }

    public static function createPathIfNeeded(string $path): void
    {
        if (!\is_dir($path)) {
            \mkdir($path, 0700);
        }
    }
}
