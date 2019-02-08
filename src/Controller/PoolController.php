<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller;

use App\Service\FilePoolUrlBuilder;
use App\Service\PathConcatenatorInterface;
use App\Service\Pool\VirtualPathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PoolController extends Controller
{
    /** @var FilePoolUrlBuilder */
    private $filePoolUrlBuilder;

    /** @var VirtualPathResolverInterface */
    private $virtualPathResolver;

    /** @var PathConcatenatorInterface */
    private $pathConcatenator;

    public function __construct(
        FilePoolUrlBuilder $filePoolUrlBuilder,
        VirtualPathResolverInterface $virtualPathResolver,
        PathConcatenatorInterface $pathConcatenator
    ) {
        $this->filePoolUrlBuilder = $filePoolUrlBuilder;
        $this->virtualPathResolver = $virtualPathResolver;
        $this->pathConcatenator = $pathConcatenator;
    }

    /**
     * @return Response|StreamedResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getAction(Request $request, string $userRoot, string $path): Response
    {
        $concatenatedPath = $this->pathConcatenator->concatTwo($userRoot, $path);

        // @TODO get user that is logged in via cookie
        // @TODO                         or via api token

        /*
        $user = $this->getLoggedInUser($request);

        if (!$this->checkPermissions($user, $request, $userRoot)) {
            return new Response('', 403);
            //throw new AccessDeniedException();
        }
        */

        // @TODO If no user is logged in, get connected screen
        //       and check if screen has a presentation scheduled that contains this file

        // @TODO Check if belongs to user that is logged in via cookie
        // @TODO    OR if belongs to user that is logged in via API Token
        // @TODO    OR if belongs to organziation that user belongs to that is logged cookie
        // @TODO    OR if belongs to organziation that user belongs to that is logged in via API Token

        try {
            $absolutePath = $this->filePoolUrlBuilder->getAbsolutePath(
                $this->virtualPathResolver->replaceVirtualBasePath($concatenatedPath)
            );

            $fileInfo = new File($absolutePath);
            $lastModifiedDate = $fileInfo->getMTime();

            $expectedLastModifiedDate = $request->headers->get('if-modified-since');
            if (null !== $expectedLastModifiedDate) {
                $expectedLastModifiedTimestamp = \strtotime($expectedLastModifiedDate);
                if ($lastModifiedDate <= $expectedLastModifiedTimestamp) {
                    return new Response(
                        \file_get_contents($absolutePath),
                        304,
                        [
                            'Content-Length' => 0,
                            'Last-Modified' => \gmdate('D, d M Y G:i:s T', $lastModifiedDate),
                        ]
                    );
                }
            }

            return new StreamedResponse(
                function () use ($absolutePath): void {
                    \readfile($absolutePath);
                },
                200,
                [
                    'Content-Type'  => $fileInfo->getMimeType(),
                    'Last-Modified' => \gmdate('D, d M Y G:i:s T', $lastModifiedDate),
                ]
            );
        } catch (FileNotFoundException $oException) {
            return new Response('', 404);
        } catch (AccessDeniedException $oException) {
            return new Response('', 403);
        }
    }
}
