<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Screen;
use AppBundle\Service\ScreenAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScreenController extends Controller
{
    public function indexAction(): Response
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();

        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'screens' => $screens,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @Route("/adm/modify_screen", name="modify-screen")
     */
    public function modifyAction(Request $request): Response
    {
        $guid = $request->get('hidGuid');
        $name = $request->get('txtName');
        $loc = $request->get('txtLocation');
        $notes = $request->get('txtNotes');
        $admin = $request->get('txtAdmin');
        $ajax = ('1' === $request->get('ajax', '0'));

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find(Screen::class, $guid);

        // Check if screen may be edited by current user
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowedTo($screen, $user, 'manager')) {
            throw new AccessDeniedException();
        }

        $screen->setName($name);
        $screen->setNotes($notes);
        $screen->setLocation($loc);
        $screen->setAdminC($admin);

        $em->persist($screen);
        $em->flush();

        // plain old form request
        if (!$ajax) {
            return $this->redirectToRoute($request->get('hidUri'));
        }

        // is AJAX request
        return $this->json(['status' => 'ok']);
    }
}
