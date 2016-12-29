<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:12
 */

namespace AppBundle\Controller\Management;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class Screens extends Controller
{
    /**
     * @Route("/manage/screens", name="management-screens")
     */
    public function indexAction(Request $request)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();

        // TODO filter, which screens may be managed by current user

        // replace this example code with whatever you need
        return $this->render('manage/screens.html.twig', [
            'screens' => $screens,
        ]);
    }


    /**
     * @Route("/manage/modify_screen", name="management-modify-screen")
     */
    public function modifyAction(Request $request)
    {
        $guid = $request->get('hidGuid');
        $name = $request->get('txtName');
        $loc = $request->get('txtLocation');
        $notes = $request->get('txtNotes');
        $admin = $request->get('txtAdmin');
        $ajax = boolval(($request->get('ajax', '0') == '1') ? true : false);

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find('\AppBundle\Entity\Screen', $guid);
        $screen->setName($name);
        $screen->setNotes($notes);
        $screen->setLocation($loc);
        $screen->setAdminC($admin);

        $em->persist($screen);
        $em->flush();

        // plain old form request
        if (!$ajax) {
            return $this->redirectToRoute('admin-screens');
        }

        // is AJAX request
        return $this->json(array('status' => 'ok'));
    }


}
