<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Presentation;

use App\Entity\Presentation;
use App\Presentation\AnyPresentationRendererInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RenderPresentationController extends Controller
{
    /** @var AnyPresentationRendererInterface */
    private $anyPresentationRenderer;

    public function __construct(
        AnyPresentationRendererInterface $anyPresentationRenderer
    ) {
        $this->anyPresentationRenderer = $anyPresentationRenderer;
    }

    public function getAction(Presentation $presentation): Response
    {
        return new Response(
            $this->anyPresentationRenderer->render($presentation),
            200
        );
    }
}
