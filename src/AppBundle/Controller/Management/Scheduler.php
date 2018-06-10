<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScheduledPresentation;
use AppBundle\Entity\Screen;
use AppBundle\Service\ScreenAssociation;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Scheduler extends Controller
{
    /**
     * @Route("/manage/scheduler", name="management-scheduler")
     */
    public function schedulerAction(): Response
    {
        // user that is logged in
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // screens that are associated to the user or to its organizations
        $assoc = $this->get('app.screenassociation');
        /** @var ScreenAssociation $assoc */
        $screens = $assoc->getScreensForUser($user);

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

    /**
     * @Route("/manage/get-schedule", name="management-get-schedule")
     */
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
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
            if (!$assoc->isUserAllowed($screen, $user)) {
                throw new AccessDeniedException();
            }

            $sf = $start->format('Y-m-d H:i:s');
            $su = $end->format('Y-m-d H:i:s');

            $query = $em->createQuery(
                'SELECT p
                    FROM AppBundle:ScheduledPresentation p
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
        $serializer = $this->get('jms_serializer');
        return new Response($serializer->serialize($sched, 'json'));
    }

    /**
     * @Route("/manage/add-scheduled", name="management-add-scheduled")
     */
    public function addScheduledAction(Request $request): Response
    {
        $guid   = $request->get('screen');
        $em     = $this->getDoctrine()->getManager();
        $screen = $em->find(Screen::class, $guid);

        // Check if user is allowed to see/edit screen
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

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

        $this->handleCollisions($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/manage/change-scheduled", name="management-change-scheduled")
     */
    public function changeScheduledAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager $em */
        $id = $request->get('id');

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));
        $guid   = $request->get('screen');
        $screen = $em->find(Screen::class, $guid);
        $s      = $em->find(ScheduledPresentation::class, $id); /** @var ScheduledPresentation $s */

        // Check if user is allowed to see/edit screen
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setScreen($screen);

        $em->persist($s);
        $em->flush(); // just for sure

        $this->handleCollisions($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/manage/delete-scheduled", name="management-delete-scheduled")
     */
    public function deleteScheduledAction(Request $request): Response
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager $em */

        /** @var ScheduledPresentation $s */
        $s = $em->find(ScheduledPresentation::class, $id);
        $screen = $s->getScreen();

        // Check if user is allowed to see/edit screen
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

        $em->remove($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    // @TODO Move this to own service.
    protected function handleCollisions(ScheduledPresentation $s): void
    {
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager $em */
        $start = $s->getScheduledStart();
        $end = $s->getScheduledEnd();

        // check if scheduled presentation encloses same/other schedule on same screen entirely
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start  >= :current_start AND p.scheduled_end <= :current_end)
                        ) AND 
                        p.screen = :screen
                        AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            // remove all that are fully enclosed
            $em->remove($o);
        }
        $em->flush();

        // check if scheduled presentation is entirely enclosed by same/other schedule on same screen
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start < :current_start AND p.scheduled_end > :current_end)
                        ) AND 
                        p.screen = :screen
                        AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /** @var ScheduledPresentation $o */
            $new_o = new ScheduledPresentation();
            $new_o->setScreen($o->getScreen());
            $new_o->setPresentation($o->getPresentation());

            // new scheduled item at end
            $new_o->setScheduledStart($s->getScheduledEnd());
            $new_o->setScheduledEnd($o->getScheduledEnd());

            // old scheduled item at start
            $o->setScheduledEnd($s->getScheduledStart());
            $em->persist($o);
            $em->persist($new_o);
        }
        $em->flush();

        // check if scheduled presentation overlaps same/other schedule on same screen
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (p.scheduled_start < :current_start AND 
                        p.scheduled_end >= :current_start AND p.scheduled_end <= :current_end)
                        AND p.screen = :screen AND p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $o->setScheduledEnd($s->getScheduledStart());
            $em->persist($o);
        }
        $em->flush();

        // check if scheduled presentation overlaps same/other schedule on same screen (second case)
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (p.scheduled_start > :current_start AND 
                        p.scheduled_start <= :current_end AND 
                        p.scheduled_end > :current_end)
                        AND p.screen = :screen AND p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $o->setScheduledStart($s->getScheduledEnd());
            $em->persist($o);
        }
        $em->flush();

        // check if scheduled presentation overlaps same presentation on same screen at start
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        p.scheduled_start  < :current_start AND
                        p.scheduled_end >= :current_start AND p.scheduled_end <= :current_end
                        ) AND 
                        p.screen = :screen AND 
                        p.presentation = :presentation AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('presentation', $s->getPresentation())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $s->setScheduledStart($o->getScheduledStart());
            $em->persist($s);
            $em->remove($o);
        }
        $em->flush();

        // check if scheduled presentation overlaps same presentation on same screen at end
        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        p.scheduled_start >= :current_start AND p.scheduled_start <= :current_end AND
                        p.scheduled_end > :current_end
                        ) AND 
                        p.screen = :screen AND 
                        p.presentation = :presentation AND 
                        p.id != :id
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('current_start', $start)
            ->setParameter('current_end', $end)
            ->setParameter('id', $s->getId())
            ->setParameter('presentation', $s->getPresentation())
            ->setParameter('screen', $s->getScreen());

        $overlaps = $query->getResult();
        foreach ($overlaps as $o) { /* @var ScheduledPresentation $o */
            $s->setScheduledEnd($o->getScheduledEnd());
            $em->persist($s);
            $em->remove($o);
        }
        $em->flush();
    }
}
