<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class OrganizationsController extends Controller
{
    /**
     * @Route("/adm/organizations", name="admin-organizations")
     */
    public function indexAction(): Response
    {
        return $this->render('adm/organizations.html.twig', []);
    }
}
