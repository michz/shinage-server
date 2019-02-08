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
use App\Repository\ScreenRepository;
use App\Service\SchedulerService;
use App\Service\ScreenAssociation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScreensController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SchedulerService */
    private $scheduler;

    /** @var ScreenRepository */
    private $screenRepository;

    /** @var ScreenAssociation */
    private $screenAssociation;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SchedulerService $scheduler,
        ScreenRepository $screenRepository,
        ScreenAssociation $screenAssociation,
        RouterInterface $router
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->scheduler = $scheduler;
        $this->screenRepository = $screenRepository;
        $this->screenAssociation = $screenAssociation;
        $this->router = $router;
    }

    public function indexAction(Request $request): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->tokenStorage->getToken()->getUser();

        // @TODO{s:5} Standardpräsentation pro Screen einstellen

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
        $user = $this->tokenStorage->getToken()->getUser();
        $rep = $this->entityManager->getRepository('App:Screen');

        $code   = $request->get('connect_code');
        $who    = $request->get('who');

        $screens = $rep->findBy(['connect_code' => $code]);
        if (0 === \count($screens)) {
            $this->addFlash('error', 'Die Anzeige konnte leider nicht hinzugefügt werden.');
            return $this->redirectToRoute('management-screens');
        }

        $screen = $screens[0]; /* @var Screen $screen */

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

        $this->addFlash('success', 'Die virtuelle Anzeige wurde erstellt.');
    }
}
