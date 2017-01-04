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
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

use AppBundle\Service\ScreenAssociation;


class HeartbeatController extends Controller
{
    /**
     * @Route("/screen-remote/heartbeat", name="screen-remote-heartbeat")
     */
    public function heartbeatAction(Request $request)
    {
        $sGuid = $request->request->get('screen', null);
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

        // check if screen is associated
        $assoc = $this->get('app.screenassociation');
        /** @var ScreenAssociation $assoc */
        $is_assoc = $assoc->isScreenAssociated($screen);


        return $this->json([
            'status'        => 'ok',
            'screen_status' => ($is_assoc) ? 'registered' : 'not_registered',
            'presentation'  => 'null',
        ]);
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
        foreach($request->files as $uploadedFile) {
            $name = $sGuid . '.png';
            $uploadedFile->move($basepath, $name);
            break;
        }

        return $this->json(['status' => 'ok']);
    }
}
