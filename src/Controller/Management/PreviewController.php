<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Screen;
use App\Entity\User;
use App\Repository\ScreenRepository;
use App\Service\UrlBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    const PLAYER_URL_BASE = 'https://player.shinage.org/player.html?current_presentation_url=';

    /** @var ScreenRepository */
    private $screenRepository;

    /** @var UrlBuilderInterface */
    private $urlBuilder;

    public function __construct(
        ScreenRepository $screenRepository,
        UrlBuilderInterface $urlBuilder
    ) {
        $this->screenRepository = $screenRepository;
        $this->urlBuilder = $urlBuilder;
    }

    public function previewAction(Request $request): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
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
