<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\ScreenRemote;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\Screen;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Service\ScreenAssociation;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HeartbeatController extends Controller
{
    const JSONP_DUMMY = 'REPLACE_JSONP_CALLBACK_DUMMY';

    public function heartbeatAction(Request $request, string $screenId): Response
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
        if (null === $screen) {
            $screen = new Screen();
            $screen->setGuid($screenId);
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $isPreview = $request->headers->get('X-PREVIEW');
        if ('1' !== $isPreview) {
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
        if (null !== $current) {
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
            'presentation'  => (null !== $presentation) ? $presentation->getId() : null,
            'last_modified' => $lastModified ? $lastModified->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
        ]);
    }

    /**
     * @deprecated
     */
    public function presentationAction(Request $request, int $id): Response
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
        if (null !== $callback) {
            if (0 === strpos($output, self::JSONP_DUMMY)) {
                $output = substr_replace($output, $callback, 0, \strlen(self::JSONP_DUMMY));
            } else {
                $output = $callback . '(' . $output . ')';
            }
        }

        return new Response($output, 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function clientFileAction(string $file): Response
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

    public function uploadScreenshotAction(Request $request): Response
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

    protected function generateUniqueConnectcode(): string
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Screen');

        $code = '';
        $unique = false;
        while (!$unique) {
            $code = $this->generateConnectcode();

            $screens = $rep->findBy(['connect_code' => $code]);
            if (0 === \count($screens)) {
                $unique = true;
            }
        }

        return $code;
    }

    protected function generateConnectcode(): string
    {
        $chars = 'abcdefghkmnpqrstuvwxyz23456789';
        $chars_n = \strlen($chars);
        $len = 8;
        $code = '';

        for ($i = 0; $i < $len; ++$i) {
            $code .= $chars[random_int(0, $chars_n - 1)];
        }

        return $code;
    }

    protected function getCurrentPresentation(Screen $screen): ?Presentation
    {
        $scheduler = $this->get('app.scheduler');
        return $scheduler->getCurrentPresentation($screen, true);
    }
}
