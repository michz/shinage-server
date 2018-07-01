<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\Screen;
use AppBundle\Entity\User;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Repository\ScreenRepository;
use AppBundle\Service\ScreenAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Dashboard extends Controller
{
    /**
     * @Route("/manage/dashboard", name="management-dashboard")
     */
    public function dashboardAction(): Response
    {
        // user that is logged in
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        /** @var ScreenRepository $screenRepository */
        $screenRepository = $this->get('app.repository.screen');
        $screens = $screenRepository->getScreensForUser($user);

        $countScreens = \count($screens);

        // no screens found
        if ($countScreens < 1) {
            return $this->render('manage/msg_no_screens.html.twig', []);
        }

        return $this->render('manage/dashboard.html.twig', [
            'screens' => $screens,
        ]);
    }

    /**
     * @Route("/manage/dashboard/preview/{screen_guid}", name="management-dashboard-preview")
     */
    public function previewAction(string $screen_guid): Response
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //$screen_guid = $request->get('screen');
        $screen = $em->find(Screen::class, $screen_guid); /** @var Screen $screen */
        if (!$screen) {
            throw new NoScreenGivenException();
        }

        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

        // get screenshot path
        $basepath = $this->getParameter('path_screenshots');
        $file_path = $basepath . '/' . $screen->getGuid() . '.png';
        if (!is_file($file_path)) {
            $file_path = $this->getParameter('kernel.root_dir') . '/Resources/img/nopic-de.svg';
        }

        $file = new File($file_path);
        $response = new Response();
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($file->getRealPath()));
        return $response;
    }
}
