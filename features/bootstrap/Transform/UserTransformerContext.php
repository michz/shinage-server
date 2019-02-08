<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Transform;

use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class UserTransformerContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

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
