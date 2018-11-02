<?php

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace spec\App\Service\Pool;

use App\Entity\User;
use App\Service\Pool\VirtualPathResolver;
use App\Service\Pool\VirtualPathResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VirtualPathResolverSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager,
        EntityRepository $repository
    ) {
        $this->beConstructedWith($entityManager);

        $entityManager
            ->getRepository(User::class)
            ->willReturn($repository);
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
        EntityRepository $repository,
        User $user
    ) {
        $path = '/user:test/file/there.txt';

        $repository
            ->findOneBy(Argument::exact(['email' => 'test']))
            ->willReturn($user);

        $user
            ->getId()
            ->willReturn(5);

        $this
            ->replaceVirtualBasePath($path)
            ->shouldReturn('/user-5/file/there.txt');
    }

    public function it_can_leave_untouched(
        EntityRepository $repository,
        User $user
    ) {
        $path = '/user-test/file/there.txt';

        $repository
            ->findOneBy(Argument::any())
            ->shouldNotBeCalled($user);

        $user
            ->getId()
            ->shouldNotBeCalled();

        $this
            ->replaceVirtualBasePath($path)
            ->shouldReturn('/user-test/file/there.txt');
    }
}
