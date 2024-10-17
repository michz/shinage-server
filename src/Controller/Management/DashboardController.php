<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Screen;
use App\Exceptions\NoScreenGivenException;
use App\Repository\ScreenRepository;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ScreenRepository $screenRepository,
        private readonly LoggedInUserRepositoryInterface $loggedInUserRepository,
    ) {
    }

    public function dashboardAction(): Response
    {
        // user that is logged in
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        // screens that are associated to the user or to its organizations
        $screens = $this->screenRepository->getScreensForUser($user);

        $countScreens = \count($screens);

        // no screens found
        if ($countScreens < 1) {
            return $this->render('manage/msg_no_screens.html.twig', []);
        }

        return $this->render('manage/dashboard.html.twig', [
            'screens' => $screens,
        ]);
    }

    public function previewAction(string $screen_guid): Response
    {
        /** @var Screen|null $screen */
        $screen = $this->entityManager->find(Screen::class, $screen_guid);
        if (null === $screen) {
            throw new NoScreenGivenException();
        }

        $this->denyAccessUnlessGranted('view_screenshot', $screen);

        // get screenshot path
        $basepath = $this->getParameter('path_screenshots');
        $file_path = $basepath . '/' . $screen->getGuid() . '.png';
        if (!\is_file($file_path)) {
            $file_path = $this->getParameter('kernel.project_dir') . '/src/Resources/private/img/nopic-de.svg';
        }

        $file = new File($file_path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(\file_get_contents($file->getRealPath()));
        return $response;
    }
}
