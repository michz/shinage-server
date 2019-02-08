<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScreenScheduleController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function indexAction(string $guid): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $screen = $this->entityManager->find(Screen::class, $guid);

        return $this->render('manage/screens/schedule.html.twig', [
            'screen' => $screen,
            'presentations' => $user->getPresentations($this->entityManager),
        ]);
    }
}
