<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\Screen;
use App\Service\ConnectCodeGeneratorInterface;
use App\Service\SchedulerService;
use App\Service\UrlBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrentForController extends Controller
{
    /** @var SchedulerService */
    private $scheduler;

    /** @var UrlBuilderInterface */
    private $urlBuilder;

    /** @var ConnectCodeGeneratorInterface */
    private $connectCodeGenerator;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        SchedulerService $scheduler,
        UrlBuilderInterface $urlBuilder,
        ConnectCodeGeneratorInterface $connectCodeGenerator,
        EntityManagerInterface $entityManager
    ) {
        $this->scheduler = $scheduler;
        $this->urlBuilder = $urlBuilder;
        $this->connectCodeGenerator = $connectCodeGenerator;
        $this->entityManager = $entityManager;
    }

    public function indexAction(Request $request, ?Screen $screen = null): Response
    {
        if (null === $screen) {
            $screen = new Screen();
            $screen->setGuid($request->get('guid'));
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->connectCodeGenerator->generateUniqueConnectcode());
            $this->entityManager->persist($screen);
        }

        $screen->setLastConnect(new \DateTime('@0'));
        $this->entityManager->flush();

        $presentation = $this->scheduler->getCurrentPresentation($screen, true);

        $url = $this->urlBuilder->getAbsoluteRouteBasedOnRequest(
            $request,
            'presentation',
            ['id' => $presentation->getId()]
        ) . '?last_modified=' . $presentation->getLastModified()->getTimestamp();

        return new Response(
            $url,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
