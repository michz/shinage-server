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
        ]);
    }

    /**
     * @Route("/manage/get-schedule", name="management-get-schedule")
     */
    public function getScheduleAction(Request $request)
    {
        // TODO Prüfen, ob Nutzer Schedule sehen/bearbeiten darf

        $guid = $request->get('screen');
        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);

        $start = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $r = array();
        if ($screen) {
            $rep = $em->getRepository('AppBundle:ScheduledPresentation');
            #$qb = $em->createQueryBuilder('ScheduledPresentation');
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
                $o->title   = $s->getName();
                $o->start   = $s->getScheduledStart()->format('Y-m-d H:i:s');
                $o->end     = $s->getScheduledEnd()->format('Y-m-d H:i:s');
                $o->screen  = $s->getScreen()->getGuid();
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
        // TODO Prüfen, ob Nutzer Schedule bearbeiten darf

        $guid   = $request->get('screen');
        $em     = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));

        $pres   = $request->get('presentation');

        $s = new ScheduledPresentation();
        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setName($pres);
        $s->setScreen($screen);
        // TODO: Fremdschlüssel zu Präsentation

        $em->persist($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/manage/change-scheduled", name="management-change-scheduled")
     */
    public function changeScheduledAction(Request $request)
    {
        // TODO Prüfen, ob Nutzer Schedule bearbeiten darf

        $em = $this->getDoctrine()->getManager();  /** @var EntityManager  $em */

        $id = $request->get('id');

        $start  = new \DateTime($request->get('start'), new \DateTimeZone('UTC'));
        $end    = new \DateTime($request->get('end'), new \DateTimeZone('UTC'));
        $name   = $request->get('presentation');
        $guid   = $request->get('screen');
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);
        $s      = $em->find('\AppBundle\Entity\ScheduledPresentation', $id); /** @var ScheduledPresentation $s */

        $s->setScheduledStart($start);
        $s->setScheduledEnd($end);
        $s->setName($name);
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
        // TODO Prüfen, ob Nutzer Schedule bearbeiten darf

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();  /** @var EntityManager  $em */
        $s = $em->find('\AppBundle\Entity\ScheduledPresentation', $id);
        $em->remove($s);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }
}