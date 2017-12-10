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
use AppBundle\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Entity\Screen;

class ScreenController extends Controller
{
    /**
     * @Route("/adm/screens", name="admin-screens")
     */
    public function indexAction()
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $screens = $rep->findAll();


        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'screens' => $screens,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @Route("/adm/modify_screen", name="modify-screen")
     */
    public function modifyAction(Request $request)
    {
        $guid = $request->get('hidGuid');
        $name = $request->get('txtName');
        $loc = $request->get('txtLocation');
        $notes = $request->get('txtNotes');
        $admin = $request->get('txtAdmin');
        $ajax = ($request->get('ajax', '0') === '1');

        $em = $this->getDoctrine()->getManager();
        $screen = $em->find(Screen::class, $guid);

        // Check if screen may be edited by current user
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        if (!$assoc->isUserAllowed($screen, $user)) {
            throw new AccessDeniedException();
        }

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
