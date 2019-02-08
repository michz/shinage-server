<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Admin;

use mztx\TodoBundle\Service\TodoList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardController extends Controller
{
    /** @var TodoList */
    private $todoList;

    public function __construct(
        TodoList $todoList
    ) {
        $this->todoList = $todoList;
    }

    public function indexAction(): Response
    {
        // @TODO Do this only in devenv!

        /** @var TodoList $todo */
        $todos = $this->todoList->getTodoList();

        // replace this example code with whatever you need
        return $this->render('adm/dashboard.html.twig', [
            'todos'     => $todos,
        ]);
    }
}
