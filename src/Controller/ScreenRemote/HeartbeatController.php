<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\PresentationInterface;
use App\Entity\Screen;
use App\Exceptions\NoScreenGivenException;
use App\Presentation\PresentationTypeRegistryInterface;
use App\Service\ConnectCodeGeneratorInterface;
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

    /** @var ConnectCodeGeneratorInterface */
    private $connectCodeGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        PresentationTypeRegistryInterface $presentationTypeRegistry,
        ScreenAssociation $screenAssociationHelper,
        RouterInterface $router,
        SchedulerService $scheduler,
        ConnectCodeGeneratorInterface $connectCodeGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->presentationTypeRegistry = $presentationTypeRegistry;
        $this->screenAssociationHelper = $screenAssociationHelper;
        $this->router = $router;
        $this->scheduler = $scheduler;
        $this->connectCodeGenerator = $connectCodeGenerator;
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
            $screen->setLastConnect(new \DateTime('@0'));
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
            'last_modified'    => $lastModified->format('Y-m-d H:i:s'),
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
        return $this->connectCodeGenerator->generateUniqueConnectcode();
    }

    protected function getCurrentPresentation(Screen $screen): ?PresentationInterface
    {
        return $this->scheduler->getCurrentPresentation($screen, true);
    }
}
