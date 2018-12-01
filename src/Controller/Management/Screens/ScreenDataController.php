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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    public function indexAction(Request $request, string $guid): Response
    {
        // TODO Check if logged in user is allowed to edit screen.

        $screen = $this->entityManager->find(Screen::class, $guid);

        $readonlyAttr = ' readonly';
        $readonly = true;

        if ($this->isGranted('manage', $screen)) {
            $readonlyAttr = '';
            $readonly = false;

            $form = $this->createFormBuilder($screen, ['translation_domain' => 'ScreenSettings'])
                ->add('name', TextType::class)
                ->add('location', TextType::class)
                ->add('adminc', TextType::class)
                ->add('notes', TextareaType::class)
                ->add('save', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->addFlash('success', 'Saved successfully');
                $this->entityManager->flush();
            }
        } else {
            $form = $this->createFormBuilder($screen, ['translation_domain' => 'ScreenSettings'])
                ->add('name', TextType::class, ['attr' => ['disabled' => 'disabled']])
                ->add('location', TextType::class, ['attr' => ['disabled' => 'disabled']])
                ->add('adminc', TextType::class, ['attr' => ['disabled' => 'disabled']])
                ->add('notes', TextareaType::class, ['attr' => ['disabled' => 'disabled']])
                ->getForm();
        }

        return $this->render('manage/screens/data.html.twig', [
            'form' => $form->createView(),
            'screen' => $screen,
            'readonlyAttr' => $readonlyAttr,
            'readonly' => $readonly,
        ]);
    }
}
