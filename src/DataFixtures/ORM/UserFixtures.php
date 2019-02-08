<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\DataFixtures\ORM;

use App\Entity\User;
use App\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;

class UserFixtures extends Fixture
{
    const USERNAME_ADMIN = 'admin@shinage.test';
    const PASSWORD_ADMIN = 'admin';

    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(
        UserManagerInterface $userManager
    ) {
        $this->userManager = $userManager;
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
        $admin->setPlainPassword(self::PASSWORD_ADMIN);
        $admin->setEnabled(true);
        $admin->addRole('ROLE_SUPER_ADMIN');

        $this->userManager->updateCanonicalFields($admin);
        $this->userManager->updatePassword($admin);

        $manager->persist($admin);
    }

    private function createOrga1(ObjectManager $manager): User
    {
        $orga = new User();
        $orga->setUserType(UserType::USER_TYPE_ORGA);
        $orga->setName('Test-Organization 1');
        $orga->setEmail('orga1@shinage.test');
        $orga->setPassword('dsfhgkjhdsfkjghdfhgkjdfhgkjdhfjkjghkjdshfgkjhdsfjkghksjdfhgkjdshfkghkjdhkjh');
        $orga->setEnabled(true);

        $this->userManager->updateCanonicalFields($orga);
        $this->userManager->updatePassword($orga);

        $manager->persist($orga);

        return $orga;
    }

    private function createOrgaUser1(ObjectManager $manager, User $orga): void
    {
        $admin = new User();
        $admin->setUserType(UserType::USER_TYPE_USER);
        $admin->setName('User 1');
        $admin->setEmail('user1@shinage.test');
        $admin->setPlainPassword('user1');
        $admin->setEnabled(true);
        $admin->addOrganization($orga);

        $this->userManager->updateCanonicalFields($admin);
        $this->userManager->updatePassword($admin);

        $manager->persist($admin);
    }
}
