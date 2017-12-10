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

class OrganizationsController extends Controller
{
    /**
     * @Route("/adm/organizations", name="admin-organizations")
     */
    public function indexAction()
    {
        return $this->render('adm/organizations.html.twig', []);
    }
}
