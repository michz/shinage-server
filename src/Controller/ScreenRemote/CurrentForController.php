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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    /** @var ScreenAssociation */
    private $screenAssociationHelper;

    public function __construct(
        SchedulerService $scheduler,
        UrlBuilderInterface $urlBuilder,
        ConnectCodeGeneratorInterface $connectCodeGenerator,
        EntityManagerInterface $entityManager,
        ScreenAssociation $screenAssociationHelper
    ) {
        $this->scheduler = $scheduler;
        $this->urlBuilder = $urlBuilder;
        $this->connectCodeGenerator = $connectCodeGenerator;
        $this->entityManager = $entityManager;
        $this->screenAssociationHelper = $screenAssociationHelper;
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
