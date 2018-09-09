<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Presentation;

use AppBundle\Entity\Presentation;
use AppBundle\Presentation\AnyPresentationRendererInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RenderPresentationController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnyPresentationRendererInterface */
    private $anyPresentationRenderer;

    public function __construct(
        EntityManagerInterface $entityManager,
        AnyPresentationRendererInterface $anyPresentationRenderer
    ) {
        $this->entityManager = $entityManager;
        $this->anyPresentationRenderer = $anyPresentationRenderer;
    }

    public function getAction(int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);
        return new Response(
            $this->anyPresentationRenderer->render($presentation),
            200
        );
    }
}
