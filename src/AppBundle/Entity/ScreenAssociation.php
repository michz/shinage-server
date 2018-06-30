<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Entity;

use AppBundle\ScreenRoleType;

class ScreenAssociation
{
    /** @var int */
    protected $id;

    /** @var Screen */
    protected $screen;

    /** @var User */
    protected $user_id;

    /** @var string */
    protected $role = ScreenRoleType::ROLE_ADMIN;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
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

    public function setUserId(User $userId = null): self
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getUserId(): User
    {
        return $this->user_id;
    }
}
