<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\Pool;

use AppBundle\Controller\Api\Exception\AccessDeniedException;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class VirtualPathResolver implements VirtualPathResolverInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function replaceVirtualBasePath(string $path): string
    {
        if (0 === \strpos($path, 'user:') || 0 === \strpos($path, '/user:')) {
            // Replace part of path with real path name instead of virtual folder name
            \preg_match('/user\:([^\/]+)/i', $path, $matches);

            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $matches[1]]);
            if (null === $user) {
                throw new AccessDeniedException();
            }

            return \preg_replace('/user\:([^\/]+)/i', 'user-' . $user->getId(), $path);
        }

        return $path;
    }
}
