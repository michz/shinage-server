<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\DataFixtures\ORM;

use App\Entity\User;
use App\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USERNAME_ADMIN = 'admin@shinage.test';
    public const PASSWORD_ADMIN = 'admin';
    public const PASSWORD_ORGA_USER = 'user1';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createAdmin($manager);
        $orga1 = $this->createOrga1($manager);
        $this->createOrgaUser1($manager, $orga1);
        $manager->flush();
    }

    private function createAdmin(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setUserType(UserType::USER_TYPE_USER);
        $admin->setName(self::USERNAME_ADMIN);
        $admin->setEmail(self::USERNAME_ADMIN);
        $admin->setEnabled(true);
        $admin->addRole('ROLE_SUPER_ADMIN');

        $encodedPassword = $this->userPasswordHasher->hashPassword($admin, self::PASSWORD_ADMIN);
        $admin->setPassword($encodedPassword);

        $manager->persist($admin);
    }

    private function createOrga1(ObjectManager $manager): User
    {
        $orga = new User();
        $orga->setUserType(UserType::USER_TYPE_ORGA);
        $orga->setName('Test-Organization 1');
        $orga->setEmail('orga1@shinage.test');
        $orga->setEnabled(true);

        $manager->persist($orga);

        return $orga;
    }

    private function createOrgaUser1(ObjectManager $manager, User $orga): void
    {
        $admin = new User();
        $admin->setUserType(UserType::USER_TYPE_USER);
        $admin->setName('User 1');
        $admin->setEmail('user1@shinage.test');
        $admin->setEnabled(true);
        $admin->addOrganization($orga);

        $encodedPassword = $this->userPasswordHasher->hashPassword($admin, self::PASSWORD_ORGA_USER);
        $admin->setPassword($encodedPassword);

        $manager->persist($admin);
    }
}
