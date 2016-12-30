<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:12
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScreenController extends Controller
{
    /**
     * @Route("/adm/screens", name="admin-screens")
     */
    public function indexAction(Request $request)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();


        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'screens' => $screens,
        ]);
    }

    /**
     * @Route("/adm/modify_screen", name="modify-screen")
     */
    public function modifyAction(Request $request)
    {
        // TODO: Check if screen may be edited by current user

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
            return $this->redirectToRoute($request->get('hidUri'));
        }

        // is AJAX request
        return $this->json(array('status' => 'ok'));
    }


}
