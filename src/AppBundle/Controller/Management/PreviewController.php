<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\User;
use AppBundle\Repository\ScreenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    public function previewAction(): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        /** @var ScreenRepository $screenRepository */
        $screenRepository = $this->get('app.repository.screen');
        $screens = $screenRepository->getScreensForUser($user);

        return $this->render('manage/preview.html.twig', [
            'screens' => $screens,
        ]);
    }
}
