<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 21.12.16
 * Time: 09:54
 */

namespace AppBundle\Controller\ScreenRemote;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScheduledPresentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentationSlide;
use AppBundle\Entity\Slide;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

use AppBundle\Service\ScreenAssociation;
use Symfony\Component\HttpFoundation\Response;

class HeartbeatController extends Controller
{
    /**
     * TODO screen-guid-requirement genauer angeben
     * @Route("/screen-remote/heartbeat/{screenId}", name="screen-remote-heartbeat", requirements={"screenId": ".*"})
     */
    public function heartbeatAction(Request $request, $screenId)
    {
        if (!$screenId) {
            return $this->json([
                'status'        => 'error',
                'error_code'    => 'NO_SCREEN_GIVEN',
                'error_message' => 'No screen was given in this request.',
            ], 500);
        }

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $screenId);
        if ($screen == null) {
            $screen = new Screen();
            $screen->setGuid($screenId);
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $screen->setLastConnect(new \DateTime());

        // check if screen is associated
        $assoc = $this->get('app.screenassociation');
        /** @var ScreenAssociation $assoc */
        $is_assoc = $assoc->isScreenAssociated($screen);
        if (!$is_assoc) {
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $em->persist($screen);
        $em->flush();

        $presentation = null;
        /* * @var ScheduledPresentation $current */
        /** @var Presentation $current */
        $current = $this->getCurrentPresentation($screen);
        if ($current != null) {
            //$presentation = $current->getPresentation();
            $presentation = $current;
        }
        return $this->json([
            'status'        => 'ok',
            'screen_status' => ($is_assoc) ? 'registered' : 'not_registered',
            'connect_code'  => $screen->getConnectCode(),
            'presentation'  => $presentation->getId(),
        ]);
    }

    /**
     * @Route("/screen-remote/presentation/{id}", name="screen-remote-presentation", requirements={"id": "\d+"})
     */
    public function presentationAction(Request $request, $id)
    {
        // @TODO Sicherheit

        $em = $this->getDoctrine()->getManager();
        $presentation = $em->find('\AppBundle\Entity\Presentation', $id);
        if (!$presentation) {
            // @TODO better error handling/output
            throw new \Exception("Presentation not found.");
        }

        // @TODO in Service auslagern
        $playable = new PlayablePresentation();
        $playable->lastModified = $presentation->getLastModified();

        /** @var Slide $slide */
        foreach ($presentation->getSlides() as $slide) {
            $playableSlide = new PlayablePresentationSlide();
            $playableSlide->title = $slide->getId();
            $playableSlide->type = "Image"; // $slide->getSlideType(); // @TODO Transformieren
            $playableSlide->duration = $slide->getDuration() * 1000;
            $playableSlide->src = $request->getSchemeAndHttpHost() .
                $this->generateUrl('screen-remote-client-file', ['file' => $slide->getFilePath()]);
            $playableSlide->transition = "none";
            $playable->slides[] = $playableSlide;
        }

        return $this->json($playable);
    }

    /**
     * @Route("/screen-remote/client-file/{file}", name="screen-remote-client-file", requirements={"file": ".*"})
     */
    public function clientFileAction(Request $request, $file)
    {
        // @TODO check somehow security

        $pool_base = realpath($this->container->getParameter('path_pool'));
        $path = realpath($pool_base . '/' . $file);
        if (substr($path, 0, strlen($pool_base)) != $pool_base) {
            throw new AccessDeniedException();
        }

        $file = new File($path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($path));
        return $response;
    }


    /**
     * @Route("/screen-remote/upload-screenshot", name="screen-remote-screenshot")
     */
    public function uploadScreenshotAction(Request $request)
    {
        // Which screen?
        $sGuid = $request->request->get('screen', null);
        if (!$sGuid) {
            throw new NoScreenGivenException();
        }

        // get path from configuration
        $basepath = $this->container->getParameter('path_screenshots');

        // move file
        foreach ($request->files as $uploadedFile) {
            $name = $sGuid . '.png';
            $uploadedFile->move($basepath, $name);
            break;
        }

        return $this->json(['status' => 'ok']);
    }


    protected function generateUniqueConnectcode()
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Screen');

        $code = '';
        $unique = false;
        while (!$unique) {
            $code = $this->generateConnectcode();

            $screens = $rep->findBy(array('connect_code' => $code));
            if (count($screens) == 0) {
                $unique = true;
            }
        }

        return $code;
    }

    protected function generateConnectcode()
    {
        $chars = "abcdefghkmnpqrstuvwxyz23456789";
        $chars_n = strlen($chars);
        $len = 8;
        $code = '';

        for ($i = 0; $i < $len; ++$i) {
            $code .= $chars[mt_rand(0, $chars_n-1)];
        }

        return $code;
    }


    protected function getCurrentPresentation(Screen $screen)
    {
        $scheduler = $this->get('app.scheduler');
        return $scheduler->getCurrentPresentation($screen, true);
    }
}
