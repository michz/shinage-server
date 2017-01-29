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

class OrganizationsController extends Controller
{
    /**
     * @Route("/adm/organizations", name="admin-organizations")
     */
    public function indexAction(Request $request)
    {

        // replace this example code with whatever you need
        return $this->render('adm/organizations.html.twig', [
            #'screens' => $screens,
        ]);
    }
}
