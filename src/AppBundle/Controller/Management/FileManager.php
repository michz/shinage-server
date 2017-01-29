<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 22.12.16
 * Time: 09:29
 */

namespace AppBundle\Controller\Management;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;
use AppBundle\Entity\User;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Service\FilePool;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FileManager extends Controller
{

    /**
    * @Route("/manage/files", name="management-files")
    */
    public function filesAction(Request $request)
    {
        return $this->render('file-manager/index.html.twig', [
        ]);
    }

    /**
     * @Route("/manage/files-download/{file}", name="management-files-download", requirements={"file": ".*"})
     */
    public function downloadAction(Request $request, $file)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var User $user */

        // check if user is allowed to see presentation
        if (!$user->isPoolFileAllowed($file)) {
            throw new AccessDeniedException();
        }

        $path = realpath($this->container->getParameter('path_pool')) . '/' . $file;

        $file = new File($this->container->getParameter('path_pool') . '/' . $file);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($path));
        return $response;
    }


    /**
     * @Route("/manage/files-el-thumbnail/{base}/{file}", name="management-files-el-thumbnail",
     *     requirements={"base": ".*", "file": ".*"})
     */
    public function elThumbnailAction(Request $request, $base, $file)
    {
        $path = realpath($this->container->getParameter('path_pool')) .
            '/.el-thumbnails/' . $base . '/' . $file;

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

    /**
     * @Route("/manage/files-connector", name="management-files-connector")
     */
    public function connectorAction(Request $request)
    {

        $response = new StreamedResponse();
        $response->setCallback(function () {
            $pool = $this->get('app.filepool'); /** @var FilePool $pool */

            #$path = $this->container->getParameter('path_pool');
            $user = $this->get('security.token_storage')->getToken()->getUser();

            // get root directories
            $paths = $pool->getUserPaths($user);
            $roots = array();

            foreach ($paths as $name => $path) {
                $basename = basename($path);
                $tmb_path = realpath($this->container->getParameter('path_pool')) .
                    '/.el-thumbnails/' . $basename . '/';

                $roots[] = array(
                    'driver'        => 'LocalFileSystem',
                    'alias'         => $name,
                    'path'          => $path,
                    'URL'           =>
                    $this->generateUrl(
                        'management-files-download',
                        array('file' => $basename)
                    ),
                    'tmbPath'       => $tmb_path,
                    'tmbURL'        =>
                    $this->generateUrl(
                        'management-files-el-thumbnail',
                        array('base' => $basename, 'file' => '')
                    ),
                    'uploadDeny'    => array('all'),            // all mime not allowed to upload
                    'uploadAllow'   => array('image'),          // mime `image` allowed
                    'uploadOrder'   => array('deny', 'allow'),  // allowed specified mime only
                    'accessControl' => 'access'                 // disable and hide dot starting files
                );
            }


            // Documentation for connector options:
            // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
            $opts = array(
                // 'debug' => true,
                'roots' => $roots
            );

            // run elFinder
            $connector = new \elFinderConnector(new \elFinder($opts));
            $connector->run();
        });

        return $response;
    }
}
