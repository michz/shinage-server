<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Api\v1;

use AppBundle\Controller\Api\Exception\AccessDeniedException;
use AppBundle\Entity\User;
use AppBundle\Service\FilePoolPermissionCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilesController extends Controller
{
    /** @var FilePoolPermissionCheckerInterface */
    private $filePoolPermissionChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FilePoolPermissionCheckerInterface $filePoolPermissionChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->filePoolPermissionChecker = $filePoolPermissionChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function getAction(string $path): Response
    {
        $this->checkPathPermissions($path);

        // @TODO if $path is file, return it (with correct mime type)
        // @TODO if $path is dir, return listing
    }

    public function putAction(string $path): Response
    {
        $this->checkPathPermissions($path);
        // @TODO if path end with slash => Exception
        // @TODO if path already exists and is directory => Exception
    }

    public function deleteAction(string $path): Response
    {
        $this->checkPathPermissions($path);
        // @TODO if path is file, delete
        // @TODO if path is directory, delete it recursively
    }

    private function checkPathPermissions(string $path): void
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (false === is_a($user, User::class)) {
            throw new AccessDeniedException('Not logged in.');
        }
        if (false === $this->filePoolPermissionChecker->mayUserAccessPath($user, $path)) {
            throw new AccessDeniedException();
        }
    }
}
