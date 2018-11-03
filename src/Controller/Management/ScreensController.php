<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Guid;
use App\Entity\Screen;
use App\Entity\ScreenAssociation as ScreenAssociationEntity;
use App\Entity\User;
use App\Form\CreateVirtualScreenForm;
use App\Repository\ScreenRepository;
use App\ScreenRoleType;
use App\Service\SchedulerService;
use App\Service\ScreenAssociation;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScreensController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SchedulerService */
    private $scheduler;

    /** @var ScreenRepository */
    private $screenRepository;

    /** @var ScreenAssociation */
    private $screenAssociation;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SchedulerService $scheduler,
        ScreenRepository $screenRepository,
        ScreenAssociation $screenAssociation
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->scheduler = $scheduler;
        $this->screenRepository = $screenRepository;
        $this->screenAssociation = $screenAssociation;
    }

    public function indexAction(Request $request): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->tokenStorage->getToken()->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // @TODO{s:5} Standardpräsentation pro Screen einstellen

        // "create virtual screen" form
        $createForm = $this->createForm(CreateVirtualScreenForm::class);
        $this->handleCreateVirtualScreen($request, $createForm);

        // make sure former changes to database are visible to getScreensForUser()
        $em->flush();

        $screens = $this->screenRepository->getScreensForUser($user);

        foreach ($screens as $screen) {
            $this->scheduler->updateScreen($screen);
        }

        return $this->render('manage/screens.html.twig', [
            'screens' => $screens,
            'screens_count' => \count($screens),
            'organizations' => $user->getOrganizations(),
            'create_form' => $createForm->createView(),
        ]);
    }

    public function connectAction(Request $request): RedirectResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('App:Screen');

        $code   = $request->get('connect_code');
        $who    = $request->get('who');

        $screens = $rep->findBy(['connect_code' => $code]);
        if (0 === \count($screens)) {
            $this->addFlash('error', 'Die Anzeige konnte leider nicht hinzugefügt werden.');
            return $this->redirectToRoute('management-screens');
        }

        $screen = $screens[0]; /* @var Screen $screen */

        $screen->setConnectCode('');
        $em->persist($screen);

        $assoc = new ScreenAssociationEntity();
        $assoc->setScreen($screen);
        $assoc->setRole(ScreenRoleType::ROLE_ADMIN);

        if ('me' === $who) {
            $assoc->setUser($user);
        } else {
            $orga = $em->find(User::class, $who);
            $assoc->setUser($orga);
        }

        $em->persist($assoc);
        $em->flush();

        $this->addFlash('success', 'Die Anzeige wurde erfolgreich hinzugefügt.');
        return $this->redirectToRoute('management-screens');
    }

    /**
     * Handles the create-form submission.
     *
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function handleCreateVirtualScreen(Request $request, Form $createForm): void
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ('POST' !== $request->getMethod() || !$request->request->has($createForm->getName())) {
            return;
        }

        $createForm->handleRequest($request);

        if (!$createForm->isSubmitted() || !$createForm->isValid()) {
            return;
        }

        $virtualScreen = new Screen();
        $virtualScreen->setGuid(Guid::generateGuid());
        $virtualScreen->setName($createForm->get('name')->getData());
        $virtualScreen->setFirstConnect(new \DateTime());
        $virtualScreen->setLastConnect(new \DateTime());
        $em->persist($virtualScreen);
        $em->flush();

        // now create association
        $this->screenAssociation->associateByString(
            $virtualScreen,
            $createForm->get('owner')->getData(),
            ScreenRoleType::ROLE_ADMIN
        );

        $this->addFlash('success', 'Die virtuelle Anzeige wurde erstellt.');
    }
}
