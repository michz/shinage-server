<?php

namespace spec\App\Service;

use App\Service\ApiRoleRegistry;
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

    public function it_can_register_role_and_get_it_back()
    {
        $key = 'key1';
        $this->registerRole($key)->getRoles()->shouldContain($key);
    }

    public function it_can_throw_exception_on_double_registration()
    {
        $key = 'key2';
        $this->registerRole($key);
        $this->shouldThrow(\Exception::class)->during('registerRole', [$key]);
    }
}
