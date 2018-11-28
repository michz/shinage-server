<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScreenDataController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function indexAction(string $guid): Response
    {
        // TODO Check if logged in user is allowed to edit screen.

        $screen = $this->entityManager->find(Screen::class, $guid);

        $readonlyAttr = ' readonly';
        $readonly = true;

        if ($this->isGranted('manage', $screen)) {
            $readonlyAttr = '';
            $readonly = false;
        }

        return $this->render('manage/screens/data.html.twig', [
            'screen' => $screen,
            'readonlyAttr' => $readonlyAttr,
            'readonly' => $readonly,
        ]);
    }

    public function saveDataAction(Request $request, string $guid): Response
    {
        $name = $request->get('txtName');
        $loc = $request->get('txtLocation');
        $notes = $request->get('txtNotes');
        $admin = $request->get('txtAdmin');

        $screen = $this->entityManager->find(Screen::class, $guid);

        // Check if screen may be edited by current user
        $this->denyAccessUnlessGranted('manage', $screen);

        $screen->setName($name);
        $screen->setNotes($notes);
        $screen->setLocation($loc);
        $screen->setAdminC($admin);

        $this->entityManager->persist($screen);
        $this->entityManager->flush();

        $this->addFlash('success', 'Screen data saved.');
        return $this->redirect($request->get('hidUri'));
    }
}
