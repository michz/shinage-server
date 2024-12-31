<?php

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace spec\App\Service\Pool;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\Pool\VirtualPathResolver;
use App\Service\Pool\VirtualPathResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VirtualPathResolverSpec extends ObjectBehavior
{
    public function let(
        UserRepositoryInterface $repository,
    ) {
        $this->beConstructedWith($repository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VirtualPathResolver::class);
    }

    public function it_implements_own_interface()
    {
        $this->shouldHaveType(VirtualPathResolverInterface::class);
    }

    public function it_can_replace_user(
        UserRepositoryInterface $repository,
        User $user,
    ) {
        $path = 'test@test.test/file/there.txt';

        $repository
            ->findOneByEmail(Argument::exact('test@test.test'))
            ->willReturn($user);

        $user
            ->getId()
            ->willReturn(5);
        $user
            ->getUserType()
            ->willReturn('user');

        $this
            ->replaceVirtualBasePath($path)
            ->shouldReturn('user-5/file/there.txt');
    }

    public function it_can_replace_organization(
        UserRepositoryInterface $repository,
        User $user,
    ) {
        $path = 'test2@test.test/file/there.txt';

        $repository
            ->findOneByEmail(Argument::exact('test2@test.test'))
            ->willReturn($user);

        $user
            ->getId()
            ->willReturn(7);
        $user
            ->getUserType()
            ->willReturn('organization');

        $this
            ->replaceVirtualBasePath($path)
            ->shouldReturn('user-7/file/there.txt');
    }

    public function it_can_leave_untouched(
        UserRepositoryInterface $repository,
        User $user,
    ) {
        $path = 'user-test/file/there.txt';

        $repository
            ->findOneByEmail(Argument::any())
            ->shouldNotBeCalled($user);

        $user
            ->getId()
            ->shouldNotBeCalled();

        $this
            ->replaceVirtualBasePath($path)
            ->shouldReturn('user-test/file/there.txt');
    }
}
