<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class ScreenAssociation
{
    /** @var int */
    protected $id;

    /** @var Screen */
    protected $screen;

    /** @var User */
    protected $user;

    /** @var array|string[] */
    protected $roles;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param array|string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setScreen(Screen $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function setUser(User $userId = null): self
    {
        $this->user = $userId;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
