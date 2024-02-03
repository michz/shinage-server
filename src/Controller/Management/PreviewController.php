<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Screen;
use App\Repository\ScreenRepository;
use App\Security\LoggedInUserRepositoryInterface;
use App\Service\UrlBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends AbstractController
{
    public const PLAYER_URL_BASE = 'https://player.shinage.org/player.html?current_presentation_url=';

    private ScreenRepository $screenRepository;

    private UrlBuilderInterface $urlBuilder;

    private LoggedInUserRepositoryInterface $loggedInUserRepository;

    public function __construct(
        ScreenRepository $screenRepository,
        UrlBuilderInterface $urlBuilder,
        LoggedInUserRepositoryInterface $loggedInUserRepository
    ) {
        $this->screenRepository = $screenRepository;
        $this->urlBuilder = $urlBuilder;
        $this->loggedInUserRepository = $loggedInUserRepository;
    }

    public function previewAction(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $screens = $this->screenRepository->getScreensForUser($user);

        return $this->render('manage/preview.html.twig', [
            'screens' => $screens,
            'previewUrls' => $this->getPresentationUrls($request, $screens),
        ]);
    }

    /**
     * @param array|Screen[] $screens
     *
     * @return array|string[]
     */
    private function getPresentationUrls(Request $request, array $screens): array
    {
        $urls = [];
        foreach ($screens as $screen) {
            $urls[$screen->getGuid()] =
                self::PLAYER_URL_BASE .
                $this->urlBuilder->getAbsoluteRouteBasedOnRequest(
                    $request,
                    'current-for',
                    ['guid' => $screen->getGuid()]
                );
        }

        return $urls;
    }
}
