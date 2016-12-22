<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 22.12.16
 * Time: 09:29
 */

namespace AppBundle\Controller\Management;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;


class Scheduler extends Controller
{

    /**
    * @Route("/manage/scheduler", name="management-scheduler")
    */
    public function schedulerAction(Request $request)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();

        return $this->render('manage/schedule.html.twig', [
            'screens' => $screens,
        ]);
    }
}