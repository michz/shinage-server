<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScreenAlarmingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function indexAction(Request $request, string $guid): Response
    {
        $screen = $this->entityManager->find(Screen::class, $guid);

        if ($this->isGranted('manage', $screen)) {
            $form = $this->createFormBuilder($screen, ['translation_domain' => 'ScreenAlarming'])
                ->add('alarming_enabled', CheckboxType::class, ['required' => false, 'empty_data' => null])
                ->add('alarming_connection_threshold', IntegerType::class, ['required' => false, 'empty_data' => 0])
                ->add('alarming_mail_targets', TextType::class, ['required' => false, 'empty_data' => ''])
                ->add('save', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('success', 'Saved successfully');
            }
        } else {
            throw new AccessDeniedException('You are not allowed to manage alarming of the given screen.');
        }

        return $this->render('manage/screens/alarming.html.twig', [
            'form' => $form->createView(),
            'screen' => $screen,
        ]);
    }
}
