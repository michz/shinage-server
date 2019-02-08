<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\User;
use App\Service\FilePool;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileManager extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FilePool */
    private $filePool;

    /** @var string */
    private $poolPath;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FilePool $filePool,
        string $poolPath
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->filePool = $filePool;
        $this->poolPath = $poolPath;
    }

    public function filesAction(): Response
    {
        return $this->render('manage/file-manager/index.html.twig', []);
    }

    public function downloadAction(string $file): Response
    {
        // @TODO replace by PoolController
        $user = $this->tokenStorage->getToken()->getUser();
        /** @var User $user */

        // check if user is allowed to see presentation
        if (!$user->isPoolFileAllowed($file)) {
            throw new AccessDeniedException();
        }

        // @TODO cleanup here and do not use getParameter() twice
        $realPathPool = \realpath($this->poolPath);
        if (false === $realPathPool) {
            throw new \RuntimeException('Pool path not found.');
        }

        $path = $realPathPool . '/' . $file;
        $file = new File($this->poolPath . '/' . $file);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(\file_get_contents($path));
        return $response;
    }

    public function elThumbnailAction(string $base, string $file): Response
    {
        // @TODO Refactor: Make method "getPoolPath" or so to avoid code duplication
        $realPoolPath = \realpath($this->poolPath);
        if (false === $realPoolPath) {
            throw new \LogicException('Pool path not found.');
        }

        $path = $realPoolPath . '/.el-thumbnails/' . $base . '/' . $file;

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        // check if user is allowed to see presentation
        if (!$user->isPoolFileAllowed($base . '/' . $file)) {
            throw new AccessDeniedException();
        }

        $file = new File($path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(\file_get_contents($path));
        return $response;
    }

    public function connectorAction(): Response
    {
        $poolBase = \realpath($this->poolPath);
        if (false === $poolBase) {
            throw new \RuntimeException('Pool path not found.');
        }

        $thumbBase = $poolBase . '/.el-thumbnails/';
        if (!\is_dir($thumbBase)) {
            @\mkdir($thumbBase, 0777, true);
        }

        $response = new StreamedResponse();
        $response->setCallback(function (): void {
            $user = $this->tokenStorage->getToken()->getUser();

            // get root directories
            $paths = $this->filePool->getUserPaths($user);
            $roots = [];

            foreach ($paths as $name => $path) {
                $basename = \basename($path['real']);
                $basenameVirtual = \basename($path['virtual']);
                $tmb_path = \realpath($this->poolPath) .
                    '/.el-thumbnails/' . $basename . '/';

                $roots[] = [
                    'driver'        => 'LocalFileSystem',
                    'alias'         => $name,
                    'path'          => $path['real'],
                    'URL'           => $this->generateUrl(
                        'pool-get',
                        ['userRoot' => $basenameVirtual, 'path' => ''],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'tmbPath'       => $tmb_path,
                    'tmbURL'        => $this->generateUrl(
                        'management-files-el-thumbnail',
                        ['base' => $basename, 'file' => '']
                    ),
                    'uploadDeny'    => ['all'],
                    'uploadAllow'   => ['image', 'video'],
                    'uploadOrder'   => ['deny', 'allow'],
                    'accessControl' => 'access',
                ];
            }

            // Documentation for connector options:
            // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
            $opts = [
                // 'debug' => true,
                'roots' => $roots,
            ];

            // run elFinder
            $connector = new \elFinderConnector(new \elFinder($opts));
            $connector->run();
        });

        return $response;
    }
}
