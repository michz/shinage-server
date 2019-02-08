<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ScreenController extends Controller
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
