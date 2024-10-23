<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\User;
use App\Security\LoggedInUserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OwnersController extends AbstractController
{
    public function __construct(
        private readonly LoggedInUserRepositoryInterface $loggedInUserRepository,
    ) {
    }

    public function getPossibleOwnersAction(): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $owners = [
            $user->getId() => (string) $user,
        ];

        /** @var User $organization */
        foreach ($user->getOrganizations() as $organization) {
            $owners[$organization->getId()] = (string) $organization;
        }

        return $this->json($owners);
    }
}
