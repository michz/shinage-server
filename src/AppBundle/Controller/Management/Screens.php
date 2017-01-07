<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:12
 */

namespace AppBundle\Controller\Management;

use AppBundle\AppBundle;
use AppBundle\Entity\Screen;
use AppBundle\Entity\User;
use AppBundle\Service\ScreenAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Screens extends Controller
{
    /**
     * @Route("/manage/screens", name="management-screens")
     */
    public function indexAction(Request $request)
    {
        // user that is logged in
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        $screens = $assoc->getScreensForUser($user);

        // replace this example code with whatever you need
        return $this->render('manage/screens.html.twig', [
            'screens' => $screens,
            'screens_count' => count($screens),
            'organizations' => $user->getOrganizations(),
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
        $screen = $em->find('\AppBundle\Entity\Screen', $guid); /** @var Screen $screen */

        // Check if screen may be edited by current user
        $user = $this->get('security.token_storage')->getToken()->getUser(); /** @var User $user */
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
            return $this->redirectToRoute('admin-screens');
        }

        // is AJAX request
        return $this->json(array('status' => 'ok'));
    }


    /**
     * @Route("/manage/connect_screen", name="management-connect-screen")
     */
    public function connectAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Screen');

        $code   = $request->get('connect_code');
        $who    = $request->get('who');

        $screens = $rep->findBy(array('connect_code' => $code));
        if (count($screens) == 0) {
            $this->addFlash('error', 'Die Anzeige konnte leider nicht hinzugefügt werden.');
            return $this->redirectToRoute('management-screens');
        }

        $screen = $screens[0]; /** @var Screen $screen */

        $screen->setConnectCode('');
        $em->persist($screen);

        $assoc = new \AppBundle\Entity\ScreenAssociation();
        $assoc->setScreen($screen);
        $assoc->setRole(\AppBundle\ScreenRoleType::ROLE_ADMIN);

        if ($who == 'me') {
            $assoc->setUserId($user);
        }
        else {
            $orga = $em->find('\AppBundle\Entity\Organization', $who);
            $assoc->setOrgaId($orga);
        }

        $em->persist($assoc);
        $em->flush();


        $this->addFlash('success', 'Die Anzeige wurde erfolgreich hinzugefügt.');
        return $this->redirectToRoute('management-screens');
    }

}
