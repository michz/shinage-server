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
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilesController extends Controller
{
    /** @var FilePoolPermissionCheckerInterface */
    private $filePoolPermissionChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var string */
    private $filePoolBasePath;

    // @TODO Perhaps use flysystem?

    public function __construct(
        FilePoolPermissionCheckerInterface $filePoolPermissionChecker,
        TokenStorageInterface $tokenStorage,
        string $filePoolBasePath
    ) {
        $this->filePoolPermissionChecker = $filePoolPermissionChecker;
        $this->tokenStorage = $tokenStorage;
        $this->filePoolBasePath = $filePoolBasePath;
    }

    public function getAction(string $path): Response
    {
        $this->checkPathPermissions($path);

        $fullPath = $this->getFullPath($path);

        // @TODO Handle If-Modified-Since Header if set
        // @TODO Handle If-Unmodified-Since Header if set

        if (is_dir($fullPath)) {
            $directoryIterator = new \DirectoryIterator($fullPath);
            $files = [];
            foreach ($directoryIterator as $file) {
                if ($file->isFile()) {
                    $files[] = $file->getFilename();
                } elseif ($file->isDir() && false === $file->isDot()) {
                    $files[] = $file->getFilename() . '/';
                }
            }
            return new Response(
                json_encode($files),
                200,
                [
                    'Content-Type' => 'text/json',
                ]
            );
        } elseif (is_file($fullPath)) {
            $file = new File($fullPath);
            return new Response(
                file_get_contents($fullPath),
                200,
                [
                    'Content-Type' => $file->getMimeType(),
                    'Content-Length' => $file->getSize(),
                    'Last-Modified' => gmdate('D, d M Y G:i:s T', $file->getMTime()),
                ]
            );
        }

        throw new FileNotFoundException($path);
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

    private function getFullPath(string $filePath): string
    {
        return $this->filePoolBasePath . $filePath;
    }
}
