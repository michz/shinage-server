<?php

namespace spec\AppBundle\Service;

use AppBundle\Service\ApiRoleRegistry;
use PhpSpec\ObjectBehavior;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  01.11.17
 * @time     :  15:05
 */
class ApiRoleRegistrySpec extends ObjectBehavior
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApiRoleRegistry::class);
    }
}
