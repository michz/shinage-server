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


class Dashboard extends Controller
{

    /**
    * @Route("/manage/dashboard", name="management-dashboard")
    */
    public function dashboardAction(Request $request)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();

        return $this->render('manage/dashboard.html.twig', [
            'screens' => $screens,
        ]);
    }
}