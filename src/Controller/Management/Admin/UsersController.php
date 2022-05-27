<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends AbstractController
{
    public function indexAction(): Response
    {
        return $this->render('adm/users.html.twig', []);
    }
}
