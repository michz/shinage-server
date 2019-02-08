<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Presentation;
use App\Entity\Screen;
use App\Entity\User;
use App\Repository\PresentationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScreenDataController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PresentationsRepository */
    private $presentationsRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        PresentationsRepository $presentationsRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->presentationsRepository = $presentationsRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function indexAction(Request $request, string $guid): Response
    {
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
                ->add('defaultPresentation', EntityType::class, [
                    'class' => Presentation::class,
                    'choices' => $this->getPresentationsChoices(),
                    'required' => false,
                ])
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

    /**
     * @return Presentation[]
     */
    private function getPresentationsChoices(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (false === $user instanceof User) {
            throw new AccessDeniedException(
                'Presentations of user could not be fetched as no valid user was found in session.'
            );
        }

        $choices = [null];
        foreach ($this->presentationsRepository->getPresentationsForsUser($user) as $presentation) {
            $choices[] = $presentation;
        }

        return $choices;
    }
}
