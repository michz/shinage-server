<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Admin;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ScreenController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function indexAction(): Response
    {
        $rep = $this->entityManager->getRepository(Screen::class);
        $screens = $rep->findAll();

        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'screens' => $screens,
        ]);
    }
}
