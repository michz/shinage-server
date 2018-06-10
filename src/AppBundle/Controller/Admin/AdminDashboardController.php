<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Service\TodoList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardController extends Controller
{
    public function indexAction(): Response
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
