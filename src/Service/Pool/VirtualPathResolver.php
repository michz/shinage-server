<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\Pool;

use App\Controller\Api\Exception\AccessDeniedException;
use App\Repository\UserRepositoryInterface;

readonly class VirtualPathResolver implements VirtualPathResolverInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function replaceVirtualBasePath(string $path): string
    {
        $result = \preg_match('#^/?(?P<mail>[^/]+@[^/]+)/(?P<path>.*)#', $path, $matches);
        if (0 < $result) {
            $mail = $matches['mail'];
            $relativePath = $matches['path'];

            $user = $this->userRepository->findOneByEmail($mail);
            if (null === $user) {
                throw new AccessDeniedException();
            }

            return 'user-' . $user->getId() . '/' . $relativePath;
        }

        return $path;
    }
}
