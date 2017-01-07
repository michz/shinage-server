<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 22.12.16
 * Time: 09:29
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\ScheduledPresentation;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;

use AppBundle\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\DateTime;

class Scheduler extends Controller
{

    /**
     * @Route("/manage/scheduler", name="management-scheduler")
     */
    public function schedulerAction(Request $request)
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
    public function getScheduleAction(Request $request)
    {
        $guid = $request->get('screen');
        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);

        // parse start and end time
        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $r = array();
        if ($screen) {
            // Check if user is allowed to see/edit screen
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
            if (!$assoc->isUserAllowed($screen, $user)) {
                throw new AccessDeniedException();
            }

            // TODO Erkenne und behandle Schnitte/Überlappungen mit vorhandenen Items

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

            foreach ($sched as $s) {
                /** @var ScheduledPresentation $s */
                $o          = new \stdClass();
                $o->id      = $s->getId();
                $o->start   = $s->getScheduledStart()->format('Y-m-d H:i:s');
                $o->end     = $s->getScheduledEnd()->format('Y-m-d H:i:s');
                $o->screen  = $s->getScreen()->getGuid();
                $o->presentation = $s->getPresentation();
                $r[]        = $o;
            }

            #$presentations = $rep->findBy(array('user_id' => $user->getId()));
        }

        // is AJAX request
        return $this->json($r);
    }

    /**
     * @Route("/manage/add-scheduled", name="management-add-scheduled")
     */
    public function addScheduledAction(Request $request)
    {
        $guid   = $request->get('screen');
        $em     = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);

        // Check if user is allowed to see/edit screen
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $pres_id = $request->get('presentation');
        $pres   = $em->find('\AppBundle\Entity\Presentation', $pres_id);

        $s = new ScheduledPresentation();
        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setScreen($screen);
        $s->setPresentation($pres);

        $em->persist($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/manage/change-scheduled", name="management-change-scheduled")
     */
    public function changeScheduledAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager  $em */

        $id = $request->get('id');

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));
        $name   = $request->get('presentation');
        $guid   = $request->get('screen');
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);
        $s      = $em->find('\AppBundle\Entity\ScheduledPresentation', $id); /** @var ScheduledPresentation $s */

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
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/manage/delete-scheduled", name="management-delete-scheduled")
     */
    public function deleteScheduledAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager  $em */

        /** @var ScheduledPresentation $s */
        $s = $em->find('\AppBundle\Entity\ScheduledPresentation', $id);
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
}