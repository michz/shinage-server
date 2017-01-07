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


        return $this->render('default/index.html.twig', [
            'base_dir'  => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'todos'     => $todos
        ]);
    }
}
