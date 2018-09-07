<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management\Screens;

use AppBundle\Entity\Screen;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ScreenScheduleController extends Controller
{
    public function indexAction(string $guid): Response
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $screen = $em->find(Screen::class, $guid);

        return $this->render('manage/screens/schedule.html.twig', [
            'screen' => $screen,
        ]);
    }
}
