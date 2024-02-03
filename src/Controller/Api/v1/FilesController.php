<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Controller\Api\Exception\AccessDeniedException;
use App\Security\LoggedInUserRepositoryInterface;
use App\Service\FilePoolPermissionCheckerInterface;
use App\Service\Pool\VirtualPathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FilesController extends AbstractController
{
    private FilePoolPermissionCheckerInterface $filePoolPermissionChecker;

    private VirtualPathResolverInterface $virtualPathResolver;

    private LoggedInUserRepositoryInterface $loggedInUserRepository;

    private string $filePoolBasePath;

    // @TODO Perhaps use flysystem?

    public function __construct(
        FilePoolPermissionCheckerInterface $filePoolPermissionChecker,
        VirtualPathResolverInterface $virtualPathResolver,
        LoggedInUserRepositoryInterface $loggedInUserRepository,
        string $filePoolBasePath
    ) {
        $this->filePoolPermissionChecker = $filePoolPermissionChecker;
        $this->virtualPathResolver = $virtualPathResolver;
        $this->loggedInUserRepository = $loggedInUserRepository;
        $this->filePoolBasePath = $filePoolBasePath;
    }

    public function getAction(Request $request, string $path): Response
    {
        $path = $this->virtualPathResolver->replaceVirtualBasePath($path);

        $this->checkPathPermissions($path);

        $fullPath = $this->getFullPath($path);

        if (\is_dir($fullPath)) {
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
                \json_encode($files),
                200,
                [
                    'Content-Type' => 'application/json',
                ]
            );
        } elseif (\is_file($fullPath)) {
            $file = new File($fullPath);

            $expectedLastModifiedDate = $request->headers->get('if-modified-since');
            if (null !== $expectedLastModifiedDate) {
                $lastModifiedDate = $file->getMTime();
                $expectedLastModifiedTimestamp = \strtotime($expectedLastModifiedDate);
                if ($lastModifiedDate < $expectedLastModifiedTimestamp) {
                    return new Response(
                        '',
                        304,
                        [
                            'Content-Length' => 0,
                            'Last-Modified' => \gmdate('D, d M Y G:i:s T', $lastModifiedDate),
                        ]
                    );
                }
            }

            return new Response(
                \file_get_contents($fullPath),
                200,
                [
                    'Content-Type' => $file->getMimeType(),
                    'Content-Length' => $file->getSize(),
                    'Last-Modified' => \gmdate('D, d M Y G:i:s T', $file->getMTime()),
                ]
            );
        }

        throw new FileNotFoundException($path);
    }

    public function putAction(Request $request, string $path): Response
    {
        if ('/' === \substr($path, -1)) {
            throw new BadRequestHttpException('File name looks like a directory.');
        }

        $path = $this->virtualPathResolver->replaceVirtualBasePath($path);
        $this->checkPathPermissions($path);
        $fullPath = $this->getFullPath($path);

        if (\is_dir($fullPath)) {
            throw new BadRequestHttpException('A directory with the same name already exists.');
        }

        $folder = \dirname($fullPath);
        if (false === \is_dir($folder)) {
            \mkdir($folder, 0777, true);
        }

        $body = $request->getContent(false);
        \file_put_contents($fullPath, $body);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    public function deleteAction(string $path): Response
    {
        $path = $this->virtualPathResolver->replaceVirtualBasePath($path);
        $this->checkPathPermissions($path);
        $fullPath = $this->getFullPath($path);

        if (\is_dir($fullPath)) {
            $deleted = @\rmdir($fullPath);
            if (false === $deleted) {
                throw new BadRequestHttpException('Directory not empty.');
            }
        } elseif (\is_file($fullPath)) {
            $deleted = @\unlink($fullPath);
            if (false === $deleted) {
                throw new BadRequestHttpException('File could not be deleted.');
            }
        } else {
            throw new NotFoundHttpException('File could not be deleted. Not found or not a regular file.');
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function checkPathPermissions(string $path): void
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        if (false === $this->filePoolPermissionChecker->mayUserAccessPath($user, $path)) {
            throw new AccessDeniedException();
        }
    }

    private function getFullPath(string $filePath): string
    {
        return $this->filePoolBasePath . $filePath;
    }
}
