<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\User;
use AppBundle\Service\ScreenAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    /**
     * @Route("/manage/preview", name="management-preview")
     */
    public function previewAction(): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        // (should be last call, so that newly created screens are recognized)
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        $screens = $assoc->getScreensForUser($user);

        return $this->render('manage/preview.html.twig', [
            'screens' => $screens,
        ]);
    }
}
