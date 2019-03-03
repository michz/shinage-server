<?php

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace spec\App\Security;

use App\Entity\User;
use App\Security\LoggedInUserRepository;
use App\Security\LoggedInUserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoggedInUserRepositorySpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($tokenStorage);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LoggedInUserRepository::class);
    }

    public function it_implements_interface(): void
    {
        $this->shouldImplement(LoggedInUserRepositoryInterface::class);
    }

    public function it_can_return_user(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        User $user
    ): void {
        $tokenStorage
            ->getToken()
            ->willReturn($token);

        $token
            ->getUser()
            ->willReturn($user);

        $this
            ->getLoggedInUserOrDenyAccess()
            ->shouldReturn($user);
    }

    public function it_can_throw_exception_if_user_is_inactive(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        User $user
    ): void {
        $tokenStorage
            ->getToken()
            ->willReturn($token);

        $token
            ->getUser()
            ->willReturn($user);

        $user
            ->isEnabled()
            ->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedException::class)
            ->during('getLoggedInUserOrDenyAccess');
    }

    public function it_can_throw_exception_if_no_user_in_token(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $tokenStorage
            ->getToken()
            ->willReturn($token);

        $token
            ->getUser()
            ->willReturn(null);

        $this
            ->shouldThrow(AccessDeniedException::class)
            ->during('getLoggedInUserOrDenyAccess');
    }

    public function it_can_throw_exception_if_no_token(
        TokenStorageInterface $tokenStorage
    ): void {
        $tokenStorage
            ->getToken()
            ->willReturn(null);

        $this
            ->shouldThrow(AccessDeniedException::class)
            ->during('getLoggedInUserOrDenyAccess');
    }
}
