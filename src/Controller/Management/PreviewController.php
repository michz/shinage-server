<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\User;
use App\Repository\ScreenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    /** @var ScreenRepository */
    private $screenRepository;

    public function __construct(
        ScreenRepository $screenRepository
    ) {
        $this->screenRepository = $screenRepository;
    }

    public function previewAction(): Response
    {
        /** @var User $user user that is logged in */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        $screens = $this->screenRepository->getScreensForUser($user);

        return $this->render('manage/preview.html.twig', [
            'screens' => $screens,
        ]);
    }
}
