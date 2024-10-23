<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Presentation;
use App\Entity\PresentationInterface;
use App\Entity\User;
use App\Presentation\PresentationTypeInterface;
use App\Presentation\PresentationTypeRegistryInterface;
use App\Repository\PresentationsRepository;
use App\Security\LoggedInUserRepositoryInterface;
use App\Service\SchedulerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class PresentationsController extends AbstractController
{
    public function __construct(
        private readonly PresentationTypeRegistryInterface $presentationTypeRegistry,
        private readonly SchedulerService $scheduler,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly PresentationsRepository $presentationsRepository,
        private readonly LoggedInUserRepositoryInterface $loggedInUserRepository,
    ) {
    }

    public function managePresentationsAction(string $viewMode = 'large'): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $presentations = $this->presentationsRepository->getPresentationsForsUser($user);

        return $this->render('manage/presentations/pres-main.html.twig', [
            'presentations' => $presentations,
            'viewMode' => $viewMode,
        ]);
    }

    public function createPresentationAction(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $presentation = new Presentation();
        $presentation->setType('slideshow');
        $form = $this->formFactory->createNamedBuilder('form_presentation', FormType::class, $presentation)
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => $this->getTypeChoices($this->presentationTypeRegistry->getPresentationTypes()),
                'translation_domain' => 'PresentationTypes',
            ])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // @TODO make owner chosable
            $presentation->setOwner($user);

            $this->entityManager->persist($presentation);
            $this->entityManager->flush();
            return $this->redirectToRoute(
                'management-presentations',
                ['_fragment' => 'title-' . $presentation->getId()]
            );
        }

        return $this->render('manage/presentations/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function deletePresentationAction(int $presentationId): Response
    {
        /** @var PresentationInterface $presentation */
        $presentation = $this->entityManager->find(Presentation::class, $presentationId);

        // Check role based access rights
        $this->denyAccessUnlessGranted('delete', $presentation);

        // delete scheduled presentations
        $this->scheduler->deleteAllScheduledPresentationsForPresentation($presentation);

        // delete presentation
        $this->entityManager->remove($presentation);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('Presentation deleted') . ': ' . $presentation->getTitle()
        );

        return $this->redirectToRoute('management-presentations');
    }

    public function savePresentationTitle(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $presentationId = $request->get('subject');
        $newTitle = $request->get('value');

        $presentation = $this->entityManager->find(Presentation::class, $presentationId);
        if (null === $presentation) {
            return new Response('Presentation not found.', 400);
        }

        if ($user !== $presentation->getOwner()) {
            return new Response('Only owner can edit title.', 403);
        }

        $presentation->setTitle(\trim($newTitle));
        $this->entityManager->flush();

        return new Response('', 204);
    }

    public function savePresentationNotes(Request $request): Response
    {
        $presentationId = $request->get('subject');
        $newDescription = $request->get('value');

        $presentation = $this->entityManager->find(Presentation::class, $presentationId);
        if (null === $presentation) {
            return new Response('Presentation not found.', 400);
        }

        $this->denyAccessUnlessGranted('edit', $presentation);

        $presentation->setNotes($newDescription);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    public function savePresentationOwner(Request $request): Response
    {
        $presentationId = $request->get('presentationId');
        $newOwnerId = $request->get('newOwnerId');

        $presentation = $this->entityManager->find(Presentation::class, $presentationId);
        if (null === $presentation) {
            return new Response('Presentation not found.', 400);
        }

        $this->denyAccessUnlessGranted('edit', $presentation);

        $newOwner = $this->entityManager->find(User::class, $newOwnerId);
        $presentation->setOwner($newOwner);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    /**
     * @param PresentationTypeInterface[] $types
     *
     * @return array<string, string>
     */
    protected function getTypeChoices(array $types): array
    {
        $ret = [];
        foreach (\array_keys($types) as $type) {
            $ret[$type] = $type;
        }

        return $ret;
    }
}
