<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use App\Repository\PresentationsRepository;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ScreenScheduleController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PresentationsRepository */
    private $presentationsRepository;

    /** @var LoggedInUserRepositoryInterface */
    private $loggedInUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PresentationsRepository $presentationsRepository,
        LoggedInUserRepositoryInterface $loggedInUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->presentationsRepository = $presentationsRepository;
        $this->loggedInUserRepository = $loggedInUserRepository;
    }

    public function indexAction(string $guid): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $screen = $this->entityManager->find(Screen::class, $guid);

        return $this->render('manage/screens/schedule.html.twig', [
            'screen' => $screen,
            'presentations' => $this->presentationsRepository->getPresentationsForsUser($user),
        ]);
    }
}
