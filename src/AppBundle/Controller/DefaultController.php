<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\TodoList;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $todo = $this->get('app.todolist');
        /** @var TodoList $todo */
        $todos = $todo->getTodoList();


        return $this->redirectToRoute('management-dashboard');
    }
}
