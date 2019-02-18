<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use Eloquent\Pathogen\FileSystem\Normalizer\FileSystemPathNormalizer;
use Eloquent\Pathogen\Path;

class PathChecker implements PathCheckerInterface
{
    /** @var \Eloquent\Pathogen\Normalizer\PathNormalizerInterface */
    private $pathNormalizer;

    public function __construct()
    {
        $this->pathNormalizer = FileSystemPathNormalizer::instance();
    }

    /**
     * {@inheritdoc}
     */
    public function inAllowedBasePaths(string $target, array $basePaths): bool
    {
        $realTarget = $this->realpath($target);
        foreach ($basePaths as $basePath) {
            $realBasePath = $this->realpath($basePath);

            // First check for exact match (same level!)
            if ($realBasePath === $realTarget) {
                return true;
            }

            // Now add a trailing slash to base (if not yet done) and check for substring match
            if ('/' !== substr($realBasePath, -1)) {
                $realBasePath .= '/';
            }

            if (0 === strpos($realTarget, $realBasePath)) {
                return true;
            }
        }

        return false;
    }

    private function realpath(string $path): string
    {
        return $this->pathNormalizer
            ->normalize(
                Path::fromString($path)
            )
            ->string();
    }
}
