<?php

namespace AppBundle;

use AppBundle\Service\ApiRoleRegistry;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;

// @TODO{s:5} Favicon + Apple-Touch-Icon

class AppBundle extends Bundle
{

    public function boot()
    {
        if (!Type::hasType(ScreenRoleType::ENUM_SCREENROLE)) {
            Type::addType(ScreenRoleType::ENUM_SCREENROLE, 'AppBundle\ScreenRoleType');
        }
        if (!Type::hasType(UserType::ENUM_USER_TYPE)) {
            Type::addType(UserType::ENUM_USER_TYPE, 'AppBundle\UserType');
        }

        // suppress error "Unknown database type enum requested, (...)"
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
    }

    protected function registerApiRoles()
    {
        /** @var ApiRoleRegistry $registry */
        $registry = $this->container->get('app.apiroleregistry');
        $registry->registerRole('FILE_UPLOAD');
    }
}
