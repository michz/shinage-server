<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ScreenController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function indexAction(): Response
    {
        $rep = $this->entityManager->getRepository('App:Screen');
        $screens = $rep->findAll();

        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'screens' => $screens,
        ]);
    }
}
