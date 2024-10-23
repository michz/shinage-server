<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\ScreenRemote;

use App\Entity\Screen;
use App\Exceptions\NoScreenGivenException;
use App\Repository\ScreenCommandRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HeartbeatController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ScreenCommandRepository $screenCommandRepository,
    ) {
    }

    public function heartbeatAction(string $screenId): Response
    {
        if (empty($screenId)) {
            return $this->json([
                'status' => 'error',
                'error_code' => 'NO_SCREEN_GIVEN',
                'error_message' => 'No screen was given in this request.',
            ], 400);
        }

        $screen = $this->entityManager->find(Screen::class, $screenId);
        if (empty($screen)) {
            throw new NotFoundHttpException();
        }

        // Get "oldest" command for screen. If none existing, just return 204 to save traffic.
        $command = $this->screenCommandRepository->getOldestCommandForScreenIfAny($screen);
        if (empty($command)) {
            return new Response('', 204);
        }

        // Mark as fetched
        $command->setFetched(new \DateTime());
        $this->entityManager->flush();

        $responseText = $this->serializer->serialize(['command' => $command], 'json');
        return new Response(
            $responseText,
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => \strlen($responseText),
            ]
        );
    }

    /**
     * @deprecated Replace by own controller?
     */
    public function uploadScreenshotAction(Request $request): Response
    {
        // Which screen?
        $sGuid = $request->request->get('screen', null);
        if (empty($sGuid)) {
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
}
