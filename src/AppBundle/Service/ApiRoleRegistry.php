<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

/**
 * @deprecated Replace by 'API scopes'
 */
class ApiRoleRegistry
{
    /** @var string[]|array */
    protected $roles = [];

    /**
     * @return string[]|array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function registerRole(string $role): self
    {
        if (array_key_exists($role, $this->roles)) {
            throw new \RuntimeException('Role ' . $role . ' already registered.');
        }
        $this->roles[$role] = $role;
        return $this;
    }
}
