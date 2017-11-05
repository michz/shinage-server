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

class UsersController extends Controller
{
    /**
     * @Route("/adm/users", name="admin-users")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('adm/users.html.twig', [
            //'screens' => $screens,
        ]);
    }
}
