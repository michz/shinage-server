<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\User;
use AppBundle\Service\FilePool;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileManager extends Controller
{
    public function filesAction(): Response
    {
        return $this->render('file-manager/index.html.twig', []);
    }

    /**
     * @throws \RuntimeException
     *
     * @Route("/manage/files-download/{file}", name="management-files-download", requirements={"file": ".*"})
     */
    public function downloadAction(string $file): Response
    {
        // @TODO replace by PoolController
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var User $user */

        // check if user is allowed to see presentation
        if (!$user->isPoolFileAllowed($file)) {
            throw new AccessDeniedException();
        }

        // @TODO cleanup here and do not use getParamter() twice
        $realPathPool = realpath($this->getParameter('path_pool'));
        if (false === $realPathPool) {
            throw new \RuntimeException('Pool path not found.');
        }
        $path = $realPathPool . '/' . $file;
        $file = new File($this->getParameter('path_pool') . '/' . $file);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($path));
        return $response;
    }

    /**
     * @Route("/manage/files-el-thumbnail/{base}/{file}", name="management-files-el-thumbnail",
     *     requirements={"base": ".*", "file": ".*"})
     */
    public function elThumbnailAction(string $base, string $file): Response
    {
        // @TODO Refactor: Make method "getPoolPath" or so to avoid code duplication
        $realPoolPath = realpath($this->getParameter('path_pool'));
        if (false === $realPoolPath) {
            throw new \LogicException('Pool path not found.');
        }
        $path = $realPoolPath . '/.el-thumbnails/' . $base . '/' . $file;

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // check if user is allowed to see presentation
        if (!$user->isPoolFileAllowed($base . '/' . $file)) {
            throw new AccessDeniedException();
        }

        $file = new File($path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($path));
        return $response;
    }

    public function connectorAction(): Response
    {
        $poolBase = realpath($this->getParameter('path_pool'));
        if (false === $poolBase) {
            throw new \RuntimeException('Pool path not found.');
        }
        $thumbBase = $poolBase . '/.el-thumbnails/';
        if (!is_dir($thumbBase)) {
            @mkdir($thumbBase, 0777, true);
        }

        $response = new StreamedResponse();
        $response->setCallback(function (): void {
            $pool = $this->get('app.filepool'); /** @var FilePool $pool */
            $user = $this->get('security.token_storage')->getToken()->getUser();

            // get root directories
            $paths = $pool->getUserPaths($user);
            $roots = [];

            foreach ($paths as $name => $path) {
                $basename = basename($path);
                $tmb_path = realpath($this->getParameter('path_pool')) .
                    '/.el-thumbnails/' . $basename . '/';

                $roots[] = [
                    'driver'        => 'LocalFileSystem',
                    'alias'         => $name,
                    'path'          => $path,
                    'URL'           => $this->generateUrl(
                        'pool-get',
                        ['userRoot' => $basename, 'path' => ''],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'tmbPath'       => $tmb_path,
                    'tmbURL'        => $this->generateUrl(
                        'management-files-el-thumbnail',
                        ['base' => $basename, 'file' => '']
                    ),
                    'uploadDeny'    => ['all'],            // all mime not allowed to upload
                    'uploadAllow'   => ['image'],          // mime `image` allowed
                    'uploadOrder'   => ['deny', 'allow'],  // allowed specified mime only
                    'accessControl' => 'access',                 // disable and hide dot starting files
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
