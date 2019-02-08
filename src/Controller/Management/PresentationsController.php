<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Presentation;
use App\Entity\PresentationInterface;
use App\Entity\User;
use App\Presentation\PresentationTypeRegistryInterface;
use App\Service\SchedulerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class PresentationsController extends Controller
{
    /** @var PresentationTypeRegistryInterface */
    private $presentationTypeRegistry;

    /** @var SchedulerService */
    private $scheduler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(
        PresentationTypeRegistryInterface $presentationTypeRegistry,
        SchedulerService $scheduler,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    ) {
        $this->presentationTypeRegistry = $presentationTypeRegistry;
        $this->scheduler = $scheduler;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function managePresentationsAction(): Response
    {
        // @TODO Security
        $user = $this->tokenStorage->getToken()->getUser();

        $pres = $this->getPresentationsForUser($user);

        return $this->render('manage/presentations/pres-main.html.twig', [
            'presentations' => $pres,
        ]);
    }

    public function createPresentationAction(Request $request): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();

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
        $presentation = $this->entityManager->find('App:Presentation', $presentationId);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user->isPresentationAllowed($presentation)) {
            throw new AccessDeniedException('User is not allowed to access presentation.');
        }

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
        $user = $this->tokenStorage->getToken()->getUser();
        $presentationId = $request->get('subject');
        $newTitle = $request->get('value');

        $presentation = $this->entityManager->find(Presentation::class, $presentationId);
        if (null === $presentation) {
            return new Response('Presentation not found.', 400);
        }

        if ($user !== $presentation->getOwner()) {
            return new Response('Only owner can edit title.', 403);
        }

        $presentation->setTitle(trim($newTitle));
        $this->entityManager->flush();

        return new Response('', 204);
    }

    public function savePresentationNotes(Request $request): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $presentationId = $request->get('subject');
        $newDescription = $request->get('value');

        $presentation = $this->entityManager->find(Presentation::class, $presentationId);
        if (null === $presentation) {
            return new Response('Presentation not found.', 400);
        }

        if ($user !== $presentation->getOwner()) {
            return new Response('Only owner can edit title.', 403);
        }

        $presentation->setNotes($newDescription);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    /**
     * @return array|PresentationInterface[]
     */
    public function getPresentationsForUser(User $user): array
    {
        return $user->getPresentations($this->entityManager);
    }

    /**
     * @param array|string[] $types
     *
     * @return array|string[]
     */
    protected function getTypeChoices(array $types): array
    {
        $ret = [];
        foreach (array_keys($types) as $type) {
            $ret[$type] = $type;
        }

        return $ret;
    }
}
