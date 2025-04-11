<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Guid;
use App\Entity\Screen;
use App\Entity\ScreenAssociation as ScreenAssociationEntity;
use App\Entity\User;
use App\Form\CreateVirtualScreenForm;
use App\Repository\ScreenRepositoryInterface;
use App\Security\LoggedInUserRepositoryInterface;
use App\Service\SchedulerService;
use App\Service\ScreenAssociation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ScreensController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SchedulerService $scheduler,
        private readonly ScreenRepositoryInterface $screenRepository,
        private readonly ScreenAssociation $screenAssociation,
        private readonly RouterInterface $router,
        private readonly LoggedInUserRepositoryInterface $loggedInUserRepository,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        // "create virtual screen" form
        $createForm = $this->createForm(CreateVirtualScreenForm::class);
        $this->handleCreateVirtualScreen($request, $createForm);

        // make sure former changes to database are visible to getScreensForUser()
        $this->entityManager->flush();

        $screens = $this->screenRepository->getScreensForUser($user);

        foreach ($screens as $screen) {
            $this->scheduler->updateScreen($screen);
        }

        return $this->render('manage/screens.html.twig', [
            'screens' => $screens,
            'screens_count' => \count($screens),
            'organizations' => $user->getOrganizations(),
            'create_form' => $createForm->createView(),
            'onlinePlayerBaseUrls' => $this->generateCurrentUrls($screens),
            'prefillConnectCode' => $request->get('connect_code', null),
        ]);
    }

    /**
     * @param Screen[] $screens
     *
     * @return string[]
     */
    private function generateCurrentUrls(array $screens): array
    {
        $urls = [];
        foreach ($screens as $screen) {
            $urls[$screen->getGuid()] = $this->getParameter('env(SHINAGE_ONLINE_PLAYER_BASE_URL)') .
                $this->router->generate(
                    'current-for',
                    ['guid' => $screen->getGuid()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return $urls;
    }

    public function connectAction(Request $request): RedirectResponse
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $rep = $this->entityManager->getRepository(Screen::class);

        $code = $request->get('connect_code');
        $who = $request->get('who');

        $screens = $rep->findBy(['connect_code' => $code]);
        if (0 === \count($screens)) {
            // @TODO Translate
            $this->addFlash('error', 'Die Anzeige konnte leider nicht hinzugefügt werden.');
            return $this->redirectToRoute('management-screens');
        }

        /** @var Screen $screen */
        $screen = $screens[0];

        $screen->setConnectCode('');
        $this->entityManager->persist($screen);

        $assoc = new ScreenAssociationEntity();
        $assoc->setScreen($screen);
        $assoc->setRoles(['schedule', 'manage', 'view_screenshot']);

        if ('me' === $who) {
            $assoc->setUser($user);
        } else {
            $orga = $this->entityManager->find(User::class, $who);
            $assoc->setUser($orga);
        }

        $this->entityManager->persist($assoc);
        $this->entityManager->flush();

        // @TODO translate
        $this->addFlash('success', 'Die Anzeige wurde erfolgreich hinzugefügt.');
        return $this->redirectToRoute('management-screens');
    }

    protected function handleCreateVirtualScreen(Request $request, FormInterface $createForm): void
    {
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
        $this->entityManager->persist($virtualScreen);
        $this->entityManager->flush();

        // now create association
        // @TODO Replace
        $this->screenAssociation->associateByString(
            $virtualScreen,
            $createForm->get('owner')->getData(),
            ['schedule', 'manage', 'view_screenshot']
        );

        // @TODO translate
        $this->addFlash('success', 'Die virtuelle Anzeige wurde erstellt.');
    }
}
