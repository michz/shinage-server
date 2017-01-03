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
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

#use \elFinder;


class FileManager extends Controller
{

    /**
    * @Route("/manage/files", name="management-files")
    */
    public function filesAction(Request $request)
    {
        //$rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        //$screens = $rep->findAll();

        // TODO nur die des Benutzers anzeigen

        return $this->render('file-manager/index.html.twig', [
            //'screens' => $screens,
        ]);
    }

    /**
     * @Route("/manage/files-download/{file}", name="management-files-download", requirements={"file": ".*"})
     */
    public function downloadAction(Request $request, $file)
    {
        // TODO check if user is allowed to access file

        // TODO set (mime) headers

        $path = $this->container->getParameter('path_pool') . $file;

        $response = new Response();
        $response->setContent(file_get_contents($path));
        return $response;
    }

    /**
     * @Route("/manage/files-connector", name="management-files-connector")
     */
    public function connectorAction(Request $request)
    {

        // TODO: get root folders for user (root of user, roots of organizations)
        // TODO: set options of elfinder-connector (espec. security related)

        $response = new StreamedResponse();
        $response->setCallback(function () {
            $path = $this->container->getParameter('path_pool');
            $user = $this->get('security.token_storage')->getToken()->getUser();

            // Documentation for connector options:
            // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
            $opts = array(
                // 'debug' => true,
                'roots' => array(
                    array(
                        'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                        'path'          => $path,                 // path to files (REQUIRED)
                        'URL'           => $this->generateUrl('management-files-download', array('file' => '')), // URL to files (REQUIRED)
                        'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                        'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
                        'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                        'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                    )
                )
            );

            // run elFinder
            $connector = new \elFinderConnector(new \elFinder($opts));
            $connector->run();
        });

        return $response;
    }
}