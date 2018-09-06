<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Admin;

use mztx\TodoBundle\Service\TodoList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardController extends Controller
{
    public function indexAction(): Response
    {
        // @TODO Do this only in devenv!

        // @TODO Inject this service via constructor, do not use DI container
        $todo = $this->get('mztx.todo.todolist');
        /** @var TodoList $todo */
        $todos = $todo->getTodoList();

        // replace this example code with whatever you need
        return $this->render('adm/dashboard.html.twig', [
            'todos'     => $todos,
        ]);
    }
}
