<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management;

use App\Entity\User;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OwnersController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private LoggedInUserRepositoryInterface $loggedInUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggedInUserRepositoryInterface $loggedInUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->loggedInUserRepository = $loggedInUserRepository;
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
