<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Setup;

use App\Entity\Api\AccessKey;
use App\Entity\RegistrationCode;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserContext implements Context
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
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
        $user->setEmail($userName);
        $user->setEnabled(true);

        $encodedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @Given There is an organization :organizationName
     */
    public function thereIsAnOrganization(string $organizationName): void
    {
        $user = new User();
        $user->setEmail($organizationName);
        $user->setPassword('nopasswordfororganizations');
        $user->setEnabled(true);
        $user->setUserType('organization');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @Given The user :user has the roles :roles
     */
    public function theUserHasTheRoles(User $user, string $roles): void
    {
        $rolesArray = \explode(',', $roles);
        foreach ($rolesArray as $role) {
            $user->addRole($role);
        }

        $this->entityManager->flush();
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

    /**
     * @Given The user :user has two factor authentication disabled at all
     */
    public function theUserHasTwoFactorAuthenticationDisabledAtAll(User $user): void
    {
        $user->setEmailAuthEnabled(false);
        $user->setGoogleAuthenticatorSecret(null);
        $this->entityManager->flush();
    }

    /**
     * @Given There is a registration code :code
     * @Given There is a registration code :code that is valid until :until
     */
    public function thereIsARegistrationCode(string $code, \DateTime $until = null): void
    {
        if (null === $until) {
            $until = new \DateTime();
            $until->add(new \DateInterval('P10Y'));
        }

        $registrationCode = new RegistrationCode();
        $registrationCode->setCode($code);
        $registrationCode->setValidUntil($until);
        $registrationCode->setCreatedDate(new \DateTime());

        $this->entityManager->persist($registrationCode);
        $this->entityManager->flush();
    }

    /**
     * @Given There is a registration code :code belonging to organization :organization
     */
    public function thereIsARegistrationCodeBelongingToOrganization(string $code, User $organization): void
    {
        $validUntil = new \DateTime();
        $validUntil->add(new \DateInterval('P10Y'));

        $registrationCode = new RegistrationCode();
        $registrationCode->setCode($code);
        $registrationCode->setValidUntil($validUntil);
        $registrationCode->setCreatedDate(new \DateTime());
        $registrationCode->setAssignOrganization($organization);

        $this->entityManager->persist($registrationCode);
        $this->entityManager->flush();
    }

    /**
     * @Given The organization :organization has automatically assignment by mail host enabled
     */
    public function theOrganizationHasAutomaticallyAssignmentByMailHostEnabled(User $organization): void
    {
        $organization->setOrgaAssignAutomaticallyByMailHost(true);
        $this->entityManager->flush();
    }
}
