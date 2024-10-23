<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ScreenOfflineController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function indexAction(string $guid): Response
    {
        $screen = $this->entityManager->find(Screen::class, $guid);

        return $this->render('manage/screens/offline.html.twig', [
            'screen' => $screen,
        ]);
    }
}
