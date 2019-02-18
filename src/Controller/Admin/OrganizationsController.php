<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OrganizationsController extends AbstractController
{
    public function indexAction(): Response
    {
        return $this->render('adm/organizations.html.twig', []);
    }
}
