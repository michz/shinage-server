<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Admin;

use App\Entity\Screen;
use App\Service\ScreenAssociation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScreenController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ScreenAssociation */
    private $screenAssociation;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScreenAssociation $screenAssociation,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->screenAssociation = $screenAssociation;
        $this->tokenStorage = $tokenStorage;
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

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
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
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$this->screenAssociation->isUserAllowedTo($screen, $user, 'manager')) {
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
