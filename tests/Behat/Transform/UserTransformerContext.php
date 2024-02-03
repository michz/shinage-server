<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Transform;

use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class UserTransformerContext implements Context
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Transform :user
     * @Transform :organization
     * @Transform /^user "([^"]+)"$/
     * @Transform /^organization "([^"]+)"$/
     * @Transform /^The user "([^"]+)"$/
     * @Transform /^the user "([^"]+)"$/
     */
    public function getUser(string $username): User
    {
        $repo = $this->entityManager->getRepository(User::class);
        return $repo->findOneBy(['username' => $username]);
    }
}
