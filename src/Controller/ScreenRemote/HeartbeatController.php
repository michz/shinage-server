<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\PresentationInterface;
use App\Entity\Screen;
use App\Exceptions\NoScreenGivenException;
use App\Presentation\PresentationTypeRegistryInterface;
use App\Service\SchedulerService;
use App\Service\ScreenAssociation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class HeartbeatController extends Controller
{
    const JSONP_DUMMY = 'REPLACE_JSONP_CALLBACK_DUMMY';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PresentationTypeRegistryInterface */
    private $presentationTypeRegistry;

    /** @var ScreenAssociation */
    private $screenAssociationHelper;

    /** @var RouterInterface */
    private $router;

    /** @var SchedulerService */
    private $scheduler;

    public function __construct(
        EntityManagerInterface $entityManager,
        PresentationTypeRegistryInterface $presentationTypeRegistry,
        ScreenAssociation $screenAssociationHelper,
        RouterInterface $router,
        SchedulerService $scheduler
    ) {
        $this->entityManager = $entityManager;
        $this->presentationTypeRegistry = $presentationTypeRegistry;
        $this->screenAssociationHelper = $screenAssociationHelper;
        $this->router = $router;
        $this->scheduler = $scheduler;
    }

    public function heartbeatAction(Request $request, string $screenId): Response
    {
        if (!$screenId) {
            return $this->json([
                'status'        => 'error',
                'error_code'    => 'NO_SCREEN_GIVEN',
                'error_message' => 'No screen was given in this request.',
            ], 500);
        }

        $screen = $this->entityManager->find(Screen::class, $screenId);
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
        $is_assoc = $this->screenAssociationHelper->isScreenAssociated($screen);
        if (!$is_assoc) {
            $screen->setConnectCode($this->generateUniqueConnectcode());
        }

        $this->entityManager->persist($screen);
        $this->entityManager->flush();

        $presentation = null;
        /** @var PresentationInterface $current */
        $current = $this->getCurrentPresentation($screen);
        if (null !== $current) {
            $presentation = $current;

            $presentationType = $this->presentationTypeRegistry->getPresentationType($presentation->getType());
            $renderer = $presentationType->getRenderer();
            $lastModified = $renderer->getLastModified($current);
        } else {
            $lastModified = new \DateTime('now');
        }

        $presentationId = null;
        $presentationUrl = null;
        if (null !== $presentation) {
            $presentationId = $presentation->getId();
            $presentationUrl = $request->getScheme() . '://' . $request->getHttpHost() .
                $this->router->generate('presentation', ['id' => $presentationId]);
        }
        return $this->json([
            'status'           => 'ok',
            'screen_status'    => $is_assoc ? 'registered' : 'not_registered',
            'connect_code'     => $screen->getConnectCode(),
            'presentation'     => $presentationId,
            'presentationUrl'  => $presentationUrl,
            'last_modified'    => $lastModified ? $lastModified->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
        ]);
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
        $this->entityManager = $this->getDoctrine()->getManager();
        $rep = $this->entityManager->getRepository('App:Screen');

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
            $code .= $chars[\random_int(0, $chars_n - 1)];
        }

        return $code;
    }

    protected function getCurrentPresentation(Screen $screen): ?PresentationInterface
    {
        return $this->scheduler->getCurrentPresentation($screen, true);
    }
}
