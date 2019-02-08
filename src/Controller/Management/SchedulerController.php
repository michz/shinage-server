<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Repository\ScreenRepository;
use App\Service\ScheduleCollisionHandlerInterface;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SchedulerController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ScreenRepository */
    private $screenRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ScheduleCollisionHandlerInterface */
    private $collisionHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ScreenRepository $screenRepository,
        SerializerInterface $serializer,
        ScheduleCollisionHandlerInterface $collisionHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->screenRepository = $screenRepository;
        $this->serializer = $serializer;
        $this->collisionHandler = $collisionHandler;
    }

    public function schedulerAction(): Response
    {
        // user that is logged in
        $user = $this->tokenStorage->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // screens that are associated to the user or to its organizations
        $screens = $this->screenRepository->getScreensForUser($user);

        $count = count($screens);

        // no screens found
        if ($count < 1) {
            return $this->render('manage/msg_no_screens.html.twig', []);
        }

        // one or more screens found, now show scheduler
        return $this->render('manage/schedule.html.twig', [
            'screens' => $screens,
            'screens_count' => $count,
            'presentations' => $user->getPresentations($em),
        ]);
    }

    public function getScheduleAction(Request $request): Response
    {
        $guid = $request->get('screen');
        $em = $this->getDoctrine()->getManager();
        $screen = $em->find(Screen::class, $guid);

        // parse start and end time
        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $sched = [];
        if ($screen) {
            // Check if user is allowed to see/edit screen
            $this->denyAccessUnlessGranted('schedule', $screen);

            $sf = $start->format('Y-m-d H:i:s');
            $su = $end->format('Y-m-d H:i:s');

            $query = $em->createQuery(
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
        return new Response($this->serializer->serialize($sched, 'json'));
    }

    public function addScheduledAction(Request $request): Response
    {
        $guid   = $request->get('screen');
        $em     = $this->getDoctrine()->getManager();
        $screen = $em->find(Screen::class, $guid);

        // Check if user is allowed to see/edit screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $pres_id = $request->get('presentation');
        $pres   = $em->find(Presentation::class, $pres_id);

        $s = new ScheduledPresentation();
        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setScreen($screen);
        $s->setPresentation($pres);

        $em->persist($s);
        $em->flush(); // just for sure

        $this->collisionHandler->handleCollisions($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    public function changeScheduledAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager(); /** @var EntityManager $em */
        $id = $request->get('id');

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));
        $guid   = $request->get('screen');
        $screen = $em->find(Screen::class, $guid);
        /** @var ScheduledPresentation $s */
        $s      = $em->find(ScheduledPresentation::class, $id);

        // Check if user is allowed to see/edit screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setScreen($screen);

        $em->persist($s);
        $em->flush(); // just for sure

        $this->collisionHandler->handleCollisions($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    public function deleteScheduledAction(Request $request): Response
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager(); /** @var EntityManager $em */

        /** @var ScheduledPresentation $s */
        $s = $em->find(ScheduledPresentation::class, $id);
        $screen = $s->getScreen();

        // Check if user is allowed to see/edit screen
        $this->denyAccessUnlessGranted('schedule', $screen);

        $em->remove($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }
}
