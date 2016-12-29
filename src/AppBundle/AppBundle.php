<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;


class AppBundle extends Bundle
{

    public function boot() {
        Type::addType('enumscreenrole', 'AppBundle\ScreenRoleType');
    }
}
