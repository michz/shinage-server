<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\User;
use App\Security\PathCheckerInterface;

class FilePoolPermissionChecker implements FilePoolPermissionCheckerInterface
{
    private PathCheckerInterface $pathChecker;

    public function __construct(
        PathCheckerInterface $pathChecker
    ) {
        $this->pathChecker = $pathChecker;
    }

    public function mayUserAccessRoot(User $user, string $root): bool
    {
        if ($root === 'user-' . $user->getId()) {
            return true;
        }

        foreach ($user->getOrganizations() as $organization) {
            if ($root === 'user-' . $organization->getId()) {
                return true;
            }
        }

        return false;
    }

    public function mayUserAccessPath(User $user, string $path): bool
    {
        $splittedPath = \explode('/', $path, 2);
        $root = $splittedPath[0];
        if (false === $this->mayUserAccessRoot($user, $root)) {
            return false;
        }

        if ('/' === $path[0]) {
            $path = \substr($path, 1);
        }

        return $this->pathChecker->inAllowedBasePaths(
            $path,
            $this->getAllowedRoots($user)
        );
    }

    /**
     * @return array|string[]
     */
    private function getAllowedRoots(User $user): array
    {
        $roots = [];
        $roots[] = 'user-' . $user->getId() . '/';

        foreach ($user->getOrganizations() as $organization) {
            $roots[] = 'user-' . $organization->getId() . '/';
        }

        return $roots;
    }
}
