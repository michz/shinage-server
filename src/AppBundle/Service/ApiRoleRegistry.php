<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 04.02.2017
 * Time: 11:13
 */

namespace AppBundle\Service;

class ApiRoleRegistry
{
    protected $roles = [];

    public function __construct()
    {
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function registerRole($role)
    {
        if (array_key_exists($role, $this->roles)) {
            throw new \Exception('Role ' . $role . ' already registered.');
        }
        $this->roles[$role] = $role;
        return $this;
    }
}
