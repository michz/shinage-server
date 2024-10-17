<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Presentation;

use App\Entity\Presentation;
use App\Presentation\AnyPresentationRendererInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RenderPresentationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AnyPresentationRendererInterface $anyPresentationRenderer,
    ) {
    }

    public function getAction(Request $request, int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);

        if (null === $presentation) {
            $settings = [];

            $connectCode = $request->get('connect_code');
            if (null !== $connectCode) {
                $settings['connectCode'] = $connectCode;
            }

            $presentation = new Presentation();
            $presentation->setId(0);
            $presentation->setType('splash');
            $presentation->setSettings(\json_encode($settings));
        }

        return new Response(
            $this->anyPresentationRenderer->render($presentation),
            200
        );
    }
}
