<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Repository\PresentationsRepository;
use App\Repository\ScreenRepository;
use App\Security\LoggedInUserRepositoryInterface;
use App\Service\ScheduleCollisionHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SchedulerController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private ScreenRepository $screenRepository;

    private SerializerInterface $serializer;

    private ScheduleCollisionHandlerInterface $collisionHandler;

    private PresentationsRepository $presentationsRepository;

    private LoggedInUserRepositoryInterface $loggedInUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScreenRepository $screenRepository,
        SerializerInterface $serializer,
        ScheduleCollisionHandlerInterface $collisionHandler,
        PresentationsRepository $presentationsRepository,
        LoggedInUserRepositoryInterface $loggedInUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->screenRepository = $screenRepository;
        $this->serializer = $serializer;
        $this->collisionHandler = $collisionHandler;
        $this->presentationsRepository = $presentationsRepository;
        $this->loggedInUserRepository = $loggedInUserRepository;
    }

    public function schedulerAction(): Response
    {
        // user that is logged in
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        // screens that are associated to the user or to its organizations
        $screens = $this->screenRepository->getScreensForUser($user);

        $count = \count($screens);

        // no screens found
        if ($count < 1) {
            return $this->render('manage/msg_no_screens.html.twig', []);
        }

        // one or more screens found, now show scheduler
        return $this->render('manage/schedule.html.twig', [
            'screens' => $screens,
            'screens_count' => $count,
            'presentations' => $this->presentationsRepository->getPresentationsForsUser($user),
        ]);
    }

    public function getScheduleAction(Request $request): Response
    {
        $guid = $request->get('screen');
        /** @var Screen|null $screen */
        $screen = $this->entityManager->find(Screen::class, $guid);

        // parse start and end time
        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $sched = [];
        if (null !== $screen) {
            // Check if user is allowed to see/edit screen
            $this->denyAccessUnlessGranted('schedule', $screen);

            $sf = $start->format('Y-m-d H:i:s');
            $su = $end->format('Y-m-d H:i:s');

            $query = $this->entityManager->createQuery(
                'SELECT p
                    FROM App:ScheduledPresentation p
                    WHERE
                        ((p.scheduled_start  >= :sf AND p.scheduled_start <= :su) OR 
                        (p.scheduled_end >= :sf AND p.scheduled_end <= :su) OR
                        (p.scheduled_start <= :sf AND p.scheduled_end >= :su)) AND 
                        p.screen = :screen
                    ORDER BY p.scheduled_start ASC'
            )
                ->setParameter('su', $su)
                ->setParameter('sf', $sf)
                ->setParameter('screen', $guid);

            $sched = $query->getResult();
        }

        // is AJAX request
        return new Response($this->serializer->serialize(
            $sched,
            'json',
            SerializationContext::create()->setGroups(['ui'])
        ));
    }

    public function editScheduledAction(Request $request): Response
    {
        $id = $request->get('scheduledPresentationId');
        if (empty($id)) {
            // Create new schedule item
            $scheduledPresentation = new ScheduledPresentation();
            $this->entityManager->persist($scheduledPresentation);
        } else {
            // Edit existing schedule item
            $scheduledPresentation = $this->entityManager->find(ScheduledPresentation::class, $id);
            if (null === $scheduledPresentation) {
                throw new NotFoundHttpException('Could not find the scheduled presentation.');
            }

            // Check if user is allowed to see/edit the screen that the presentation is currently scheduled on
            $this->denyAccessUnlessGranted('schedule', $scheduledPresentation->getScreen());

            // Check if user is allowed to see/edit the presentation is currently scheduled
            $this->denyAccessUnlessGranted('schedule', $scheduledPresentation->getPresentation());
        }

        $guid = $request->get('screen');
        $screen = $this->entityManager->find(Screen::class, $guid);

        // Check if user is allowed to see/edit the (newly chosen) screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $pres_id = $request->get('presentation');
        $pres = $this->entityManager->find(Presentation::class, $pres_id);

        // Check if user is allowed to see/edit the (newly chosen) presentation
        $this->denyAccessUnlessGranted('schedule', $pres);

        $scheduledPresentation->setScheduledStart($start);
        $scheduledPresentation->setScheduledEnd($end);
        $scheduledPresentation->setScreen($screen);
        $scheduledPresentation->setPresentation($pres);

        // To be sure the changes are flushed to database before doing the collision detection on the database
        $this->entityManager->flush();

        $this->collisionHandler->handleCollisions($scheduledPresentation);
        $this->entityManager->flush();

        return $this->json(['status' => 'ok']);
    }

    public function changeScheduledAction(Request $request): Response
    {
        $id = $request->get('id');

        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));
        $guid = $request->get('screen');
        $screen = $this->entityManager->find(Screen::class, $guid);
        /** @var ScheduledPresentation $s */
        $s = $this->entityManager->find(ScheduledPresentation::class, $id);

        // Check if user is allowed to see/edit screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setScreen($screen);

        $this->entityManager->persist($s);
        $this->entityManager->flush(); // just for sure

        $this->collisionHandler->handleCollisions($s);
        $this->entityManager->flush();

        return $this->json(['status' => 'ok']);
    }

    public function deleteScheduledAction(Request $request): Response
    {
        $id = $request->get('id');

        /** @var ScheduledPresentation $s */
        $s = $this->entityManager->find(ScheduledPresentation::class, $id);
        $screen = $s->getScreen();

        // Check if user is allowed to see/edit screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $this->entityManager->remove($s);
        $this->entityManager->flush();

        return $this->json(['status' => 'ok']);
    }
}
