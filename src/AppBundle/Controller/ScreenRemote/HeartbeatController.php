<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 21.12.16
 * Time: 09:54
 */

namespace AppBundle\Controller\ScreenRemote;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

class HeartbeatController extends Controller
{
    /**
     * @Route("/screen-remote/heartbeat", name="screen-remote-heartbeat")
     */
    public function heartbeatAction(Request $request)
    {
        $sGuid = $request->query->get('screen_guid', null);
        if (!$sGuid) {
            throw new NoScreenGivenException();
        }

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $sGuid);
        if ($screen == null) {
            $screen = new Screen();
            $screen->setGuid($sGuid);
            $screen->setFirstConnect(new \DateTime());
        }

        $screen->setLastConnect(new \DateTime());

        $em->persist($screen);
        $em->flush();


        return $this->render('screen-remote/heartbeat.html.twig', [
            'current_presentation' => 'null',
        ]);
    }


    /**
     * @Route("/screen-remote/upload-screenshot", name="screen-remote-heartbeat")
     */
    public function uploadScreenshotAction(Request $request)
    {
        // TODO read out image from content and save

        $sGuid = $request->query->get('screen_guid', null);
        if (!$sGuid) {
            throw new NoScreenGivenException();
        }

        return $this->json(['status' => 'ok']);
    }
}
