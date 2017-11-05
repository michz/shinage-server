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

class AdminDashboardController extends Controller
{
    /**
     * @Route("/adm", name="admin-dashboard")
     */
    public function indexAction(Request $request)
    {
        $todo = $this->get('app.todolist');
        /** @var TodoList $todo */
        $todos = $todo->getTodoList();

        // replace this example code with whatever you need
        return $this->render('adm/dashboard.html.twig', [
            'todos'     => $todos,
        ]);
    }
}
