<?php
declare(strict_types=1);

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
     * @Given /^There is a user with username "([^"]*)" and password "([^"]*)"$/
     * @Given /^There is a user with username "([^"]*)"$/
     * @Given /^There is an organization with name "([^"]*)"$/
     */
    public function thereIsAUserWithUsernameAndPassword(string $userName, string $password = '')
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
     * @Given /^The user "([^"]*)" has the roles "([^"]*)"$/
     */
    public function theUserHasTheRoles(string $userName, string $roles)
    {
        $rolesArray = explode(',', $roles);
        /** @var User $user */
        $users = $this->entityManager->getRepository(User::class)->findBy(['username' => $userName]);
        if (\count($users) < 1) {
            throw new \Exception("Cannot add roles to user '$userName', reason: Not found.");
        }
        $user = $users[0];
        foreach ($rolesArray as $role) {
            $user->addRole($role);
        }
        $this->userManager->updateUser($user);
    }

    /**
     * @Given /^The user "([^"]*)" has an api key "([^"]*)" with scope "([^"]*)"$/
     */
    public function theUserHasAnApiKeyWithScope(string $userName, string $code, string $apiScope)
    {
        $users = $this->entityManager->getRepository(User::class)->findBy(['username' => $userName]);
        if (\count($users) < 1) {
            throw new \Exception("Cannot add roles to user '$userName', reason: Not found.");
        }
        $user = $users[0];

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
    public function theUserBelongsToTheOrganization(User $user, User $organization)
    {
        $user->getOrganizations()->add($organization);
        $organization->getUsers()->add($user);
        $this->entityManager->flush();
    }
}
