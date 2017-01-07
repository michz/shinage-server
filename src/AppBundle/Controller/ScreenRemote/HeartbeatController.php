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

        return $this->json([
            'status'        => 'ok',
            'screen_status' => ($is_assoc) ? 'registered' : 'not_registered',
            'connect_code'  => $screen->getConnectCode(),
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


    protected function generateUniqueConnectcode() {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Screen');

        $code = '';
        $unique = false;
        while(!$unique) {
            $code = $this->generateConnectcode();

            $screens = $rep->findBy(array('connect_code' => $code));
            if (count($screens) == 0) $unique = true;
        }

        return $code;
    }

    protected function generateConnectcode() {
        $chars = "abcdefghkmnpqrstuvwxyz23456789";
        $chars_n = strlen($chars);
        $len = 8;
        $code = '';

        for ($i = 0; $i < $len; ++$i) {
            $code .= $chars[mt_rand(0, $chars_n-1)];
        }

        return $code;
    }
}
