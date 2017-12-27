<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 21.12.16
 * Time: 09:54
 */

namespace AppBundle\Controller\ScreenRemote;

use AppBundle\Entity\Presentation;
use AppBundle\Service\PresentationBuilders\PresentationBuilderChain;
use JMS\Serializer\SerializerInterface;
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
    const JSONP_DUMMY = 'REPLACE_JSONP_CALLBACK_DUMMY';

    /**
     * TODO screen-guid-requirement genauer angeben
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \AppBundle\Exceptions\NoSuitablePresentationBuilderFoundException
     *
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
        $screen = $em->find(Screen::class, $screenId);
        if ($screen === null) {
            $screen = new Screen();
            $screen->setGuid($screenId);
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $isPreview = $request->headers->get('X-PREVIEW');
        if ($isPreview !== '1') {
            $screen->setLastConnect(new \DateTime());
        }

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
        /** @var Presentation $current */
        $current = $this->getCurrentPresentation($screen);
        if ($current !== null) {
            $presentation = $current;

            /** @var PresentationBuilderChain $playableBuilderChain */
            $playableBuilderChain = $this->get('app.presentation_builder_chain');
            $playableBuilder = $playableBuilderChain->getBuilderForPresentation($current);
            $lastModified = $playableBuilder->getLastModified($current);
        } else {
            $lastModified = new \DateTime('now');
        }

        return $this->json([
            'status'        => 'ok',
            'screen_status' => $is_assoc ? 'registered' : 'not_registered',
            'connect_code'  => $screen->getConnectCode(),
            'presentation'  => $presentation->getId(),
            'last_modified' => $lastModified ? $lastModified->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
        ]);
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     *
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \AppBundle\Exceptions\NoSuitablePresentationBuilderFoundException
     * @throws \Exception
     *
     * @Route("/screen-remote/presentation/{id}", name="screen-remote-presentation", requirements={"id": "\d+"})
     */
    public function presentationAction(Request $request, $id)
    {
        // @TODO Sicherheit

        $em = $this->getDoctrine()->getManager();
        $presentation = $em->find(Presentation::class, $id);
        if (!$presentation) {
            // @TODO better error handling/output
            throw new \RuntimeException('Presentation not found.');
        }

        /** @var PresentationBuilderChain $playableBuilderChain */
        $playableBuilderChain = $this->get('app.presentation_builder_chain');
        $playableBuilder = $playableBuilderChain->getBuilderForPresentation($presentation);
        $playable = $playableBuilder->buildPresentation($presentation);

        /** @var SerializerInterface $serializer */
        $serializer = $this->get('jms_serializer');
        if (\is_string($playable)) {
            $output = $playable;
        } else {
            $output = $serializer->serialize($playable, 'json');
        }

        $callback = $request->get('callback');
        if ($callback !== null) {
            if (0 === strpos($output, self::JSONP_DUMMY)) {
                $output = substr_replace($output, $callback, 0, \strlen(self::JSONP_DUMMY));
            } else {
                $output = $callback.'('.$output.')';
            }
        }

        return new Response($output, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     *
     * @Route("/screen-remote/client-file/{file}", name="screen-remote-client-file", requirements={"file": ".*"})
     */
    public function clientFileAction(/** @scrutinizer ignore-unused */ Request $request, $file)
    {
        // @TODO check somehow security

        $pool_base = realpath($this->getParameter('path_pool'));
        $path = realpath($pool_base . '/' . $file);
        if (0 !== strpos($path, $pool_base)) {
            throw new AccessDeniedException();
        }

        $file = new File($path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($path));
        return $response;
    }


    /**
     * @throws \AppBundle\Exceptions\NoScreenGivenException
     *
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
        $basepath = $this->getParameter('path_screenshots');

        // move file
        foreach ($request->files as $uploadedFile) {
            $name = $sGuid . '.png';
            $uploadedFile->move($basepath, $name);
            break;
        }

        return $this->json(['status' => 'ok']);
    }

    /**
     * @return string
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function generateUniqueConnectcode(): string
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Screen');

        $code = '';
        $unique = false;
        while (!$unique) {
            $code = $this->generateConnectcode();

            $screens = $rep->findBy(array('connect_code' => $code));
            if (\count($screens) === 0) {
                $unique = true;
            }
        }

        return $code;
    }

    /**
     * @return string
     */
    protected function generateConnectcode(): string
    {
        $chars = 'abcdefghkmnpqrstuvwxyz23456789';
        $chars_n = \strlen($chars);
        $len = 8;
        $code = '';

        for ($i = 0; $i < $len; ++$i) {
            $code .= $chars[random_int(0, $chars_n-1)];
        }

        return $code;
    }

    /**
     * @param Screen $screen
     *
     * @return mixed
     */
    protected function getCurrentPresentation(Screen $screen)
    {
        $scheduler = $this->get('app.scheduler');
        return $scheduler->getCurrentPresentation($screen, true);
    }
}
