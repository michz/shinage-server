<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;


class AppBundle extends Bundle
{

    public function boot()
    {
        if (!Type::hasType("enumscreenrole")) {
            Type::addType('enumscreenrole', 'AppBundle\ScreenRoleType');
        }

        // suppress error "Unknown database type enum requested, (...)"
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

    }
}
