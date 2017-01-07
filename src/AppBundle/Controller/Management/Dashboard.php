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
use AppBundle\Service\ScreenAssociation;


class Dashboard extends Controller
{

    /**
    * @Route("/manage/dashboard", name="management-dashboard")
    */
    public function dashboardAction(Request $request)
    {
        // user that is logged in
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        $screens = $assoc->getScreensForUser($user);

        return $this->render('manage/dashboard.html.twig', [
            'screens' => $screens,
        ]);
    }
}