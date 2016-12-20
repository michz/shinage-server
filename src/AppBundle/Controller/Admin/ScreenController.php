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
        // replace this example code with whatever you need
        return $this->render('adm/screens.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
}
