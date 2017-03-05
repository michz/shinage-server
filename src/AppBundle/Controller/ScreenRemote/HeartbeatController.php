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
use AppBundle\Service\TemplateManager;
use AppBundle\Service\TemplateRenderer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

use AppBundle\Service\ScreenAssociation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HeartbeatController extends Controller
{
    /**
     * @Route("/screen-remote/heartbeat", name="screen-remote-heartbeat")
     */
    public function heartbeatAction(Request $request)
    {
        $sGuid = $request->get('screen', null);
        if (!$sGuid) {
            throw new NoScreenGivenException();
        }

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $sGuid);
        if ($screen == null) {
            $screen = new Screen();
            $screen->setGuid($sGuid);
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $screen->setLastConnect(new \DateTime());

        $em->persist($screen);
        $em->flush();

        // check if screen is associated
        $assoc = $this->get('app.screenassociation');
        /** @var ScreenAssociation $assoc */
        $is_assoc = $assoc->isScreenAssociated($screen);

        $presentation = null;
        /** @var Presentation $presentation */
        $presentation = $this->getCurrentPresentation($screen);

        return $this->json([
            'status'        => 'ok',
            'screen_status' => ($is_assoc) ? 'registered' : 'not_registered',
            'connect_code'  => $screen->getConnectCode(),
            'presentation'  => $presentation,
        ]);
    }


    /**
     * @Route("/screen-remote/client/{guid}", name="screen-remote-client")
     */
    public function clientAction(Request $request, $guid)
    {
        // Which screen?
        if (!$guid) {
            throw new NoScreenGivenException();
        }

        /** @var TemplateManager $manager */
        $manager = $this->container->get('app.template.manager');
        /** @var TemplateRenderer $renderer */
        $renderer = $this->container->get('app.template.renderer');

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);
        if ($screen == null) {
            throw new NoScreenGivenException();
        }

        /** @var Presentation $presentation */
        $presentation = $this->getCurrentPresentation($screen);
        $slides_json = '[]';
        if ($presentation != null) {
            $slides_json = json_encode($presentation->getSlides()->getValues());
        }

        $tpl = $manager->getTemplate($presentation->getTemplate());
        return $renderer->render($tpl, [
            'screen' => $screen,
            'presentation'  => $presentation,
            'slides_json'  => $slides_json,
        ]);
/*
        return $this->render('presentations/framework.html.twig', [
            'screen' => $screen,
            'presentation'  => $presentation,
            'slides_json'  => $slides_json,
        ]);
*/
    }

    /**
     * @Route("/screen-remote/client-file/{file}", name="screen-remote-client-file", requirements={"file": ".*"})
     */
    public function clientFileAction(Request $request, $file)
    {
        // TODO check somehow security

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
