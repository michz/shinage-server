<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\Screen;
use App\Service\ConnectCodeGeneratorInterface;
use App\Service\SchedulerService;
use App\Service\ScreenAssociation;
use App\Service\UrlBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CurrentForController extends AbstractController
{
    public function __construct(
        private readonly SchedulerService $scheduler,
        private readonly UrlBuilderInterface $urlBuilder,
        private readonly ConnectCodeGeneratorInterface $connectCodeGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ScreenAssociation $screenAssociationHelper,
    ) {
    }

    public function indexAction(Request $request, ?Screen $screen = null): Response
    {
        if (null === $screen) {
            $guid = $request->get('guid');
            if (null === $guid) {
                throw new BadRequestHttpException('Parameter `guid` is missing.');
            }

            $screen = new Screen();
            $screen->setGuid($guid);
            $screen->setFirstConnect(new \DateTime());
            $screen->setConnectCode($this->connectCodeGenerator->generateUniqueConnectcode());
            $this->entityManager->persist($screen);
        }

        $screen->setLastConnect(new \DateTime('now'));
        $this->entityManager->flush();

        $presentation = $this->scheduler->getCurrentPresentation($screen, true);

        $parameters = ['last_modified' => $presentation->getLastModified()->getTimestamp()];
        if (false === $this->screenAssociationHelper->isScreenAssociated($screen)) {
            $parameters['connect_code'] = $screen->getConnectCode();
        }

        $url = $this->urlBuilder->getAbsoluteRouteBasedOnRequest(
            $request,
            'presentation',
            ['id' => $presentation->getId()]
        ) . '?' . \http_build_query($parameters);

        return new Response(
            $url,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
