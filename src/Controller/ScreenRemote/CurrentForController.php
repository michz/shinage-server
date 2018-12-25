<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\Screen;
use App\Service\SchedulerService;
use App\Service\UrlBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrentForController extends Controller
{
    /** @var SchedulerService */
    private $scheduler;

    /** @var UrlBuilderInterface */
    private $urlBuilder;

    public function __construct(
        SchedulerService $scheduler,
        UrlBuilderInterface $urlBuilder
    ) {
        $this->scheduler = $scheduler;
        $this->urlBuilder = $urlBuilder;
    }

    public function indexAction(Request $request, Screen $screen): Response
    {
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
