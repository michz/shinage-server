<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\Pool;

use App\Controller\Api\Exception\AccessDeniedException;
use App\Entity\User;
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
        $result = \preg_match('#^/?(?P<type>user|organization):(?P<mail>[^/]+)/(?P<path>.*)#', $path, $matches);

        if (0 < $result) {
            $mail = $matches['mail'];
            //$cleanPath = $matches['path'];
            $type = $matches['type'];

            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $mail]);
            if (null === $user) {
                throw new AccessDeniedException();
            }

            return \preg_replace('/' . $type . '\:([^\/]+)/i', $type . '-' . $user->getId(), $path);
        }

        return $path;
    }
}
