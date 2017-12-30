<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use AppBundle\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  27.10.17
 * @time     :  08:27
 */
class UserFixtures extends Fixture
{
    const USERNAME_ADMIN = 'admin@shinage.dev';
    const PASSWORD_ADMIN = 'admin';

    public function load(ObjectManager $manager)
    {
        $this->createAdmin($manager);
        $manager->flush();
    }

    protected function createAdmin(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setUserType(UserType::USER_TYPE_USER);
        $admin->setName(self::USERNAME_ADMIN);
        $admin->setEmail(self::USERNAME_ADMIN);
        $admin->setPlainPassword(self::PASSWORD_ADMIN);
        $admin->setEnabled(true);
        $admin->addRole('ROLE_SUPER_ADMIN');

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updateCanonicalFields($admin);
        $userManager->updatePassword($admin);

        $manager->persist($admin);
    }
}
