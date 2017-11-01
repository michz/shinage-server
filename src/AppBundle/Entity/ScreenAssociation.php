<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:21
 */

namespace AppBundle\Entity;

use AppBundle\ScreenRoleType;

/**
 * AppBundle\Entity\ScreenAssociation
 */

class ScreenAssociation
{
    /** @var int */
    protected $id;

    /** @var Screen */
    protected $screen;

    /** @var User */
    protected $user_id;

    /** @var ScreenRoleType */
    protected $role;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param ScreenRoleType $role
     *
     * @return ScreenAssociation
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return ScreenRoleType
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set screen
     *
     * @param \AppBundle\Entity\Screen $screen
     *
     * @return ScreenAssociation
     */
    public function setScreen(\AppBundle\Entity\Screen $screen)
    {
        $this->screen = $screen;

        return $this;
    }

    /**
     * Get screen
     *
     * @return \AppBundle\Entity\Screen
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * Set userId
     *
     * @param \AppBundle\Entity\User $userId
     *
     * @return ScreenAssociation
     */
    public function setUserId(\AppBundle\Entity\User $userId = null)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}
