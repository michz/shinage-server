<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use App\Entity\Api\AccessKey;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class UserContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManagerInterface $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    /**
     * @Given There is a user with username :userName and password :password
     * @Given There is a user with username :userName
     * @Given There is an organization with name :userName
     */
    public function thereIsAUserWithUsernameAndPassword(string $userName, string $password = ''): void
    {
        if (empty($password)) {
            $password = 'ThisIsATestPassword';
        }

        $user = new User();
        $user->setUsername($userName);
        $user->setEmail($userName);
        $user->setPlainPassword($password);
        $user->setEnabled(true);

        $this->userManager->updatePassword($user);
        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updateUser($user);
    }

    /**
     * @Given The user :user has the roles :roles
     */
    public function theUserHasTheRoles(User $user, string $roles): void
    {
        $rolesArray = explode(',', $roles);
        foreach ($rolesArray as $role) {
            $user->addRole($role);
        }

        $this->userManager->updateUser($user);
    }

    /**
     * @Given The user :user has an api key :code with scope :apiScope
     */
    public function theUserHasAnApiKeyWithScope(User $user, string $code, string $apiScope): void
    {
        $apiKey = new AccessKey();
        $apiKey->setOwner($user);
        $apiKey->setCode($code);
        $apiKey->setRoles([$apiScope]);
        $apiKey->setName('testkey');

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();
    }

    /**
     * @Given The user :user belongs to the organization :organization
     */
    public function theUserBelongsToTheOrganization(User $user, User $organization): void
    {
        $user->getOrganizations()->add($organization);
        $organization->getUsers()->add($user);
        $this->entityManager->flush();
    }
}
