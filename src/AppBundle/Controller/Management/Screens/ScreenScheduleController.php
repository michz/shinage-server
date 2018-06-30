<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management\Screens;

use AppBundle\Entity\Screen;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ScreenScheduleController extends Controller
{
    /**
     * @Route("/manage/screen/{guid}/schedule", name="management-screen-schedule", requirements={"guid": "[^/]*"})
     */
    public function indexAction(string $guid): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $screen = $em->find(Screen::class, $guid);

        return $this->render('manage/screens/schedule.html.twig', [
            'screen' => $screen,
        ]);
    }
}
