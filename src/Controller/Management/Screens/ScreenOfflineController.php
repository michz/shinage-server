<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ScreenOfflineController extends Controller
{
    public function indexAction(string $guid): Response
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $screen = $em->find(Screen::class, $guid);

        return $this->render('manage/screens/offline.html.twig', [
            'screen' => $screen,
        ]);
    }
}
