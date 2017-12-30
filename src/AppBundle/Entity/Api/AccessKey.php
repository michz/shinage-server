<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  02.02.17
 * @time     :  19:45
 */

namespace AppBundle\Entity\Api;

use AppBundle\Entity\User;

/**
 * AppBundle\Entity\Api\AccessKey
 */
class AccessKey
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $name;

    /** @var \DateTime */
    protected $last_use;

    /** @var array */
    protected $roles;

    /** @var User */
    protected $owner;


    /**
     * Generates a new code and sets it.
     * @return AccessKey
     */
    public function generateAndSetCode()
    {
        $code = sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        $this->setCode($code);
        return $this;
    }

    public function getRolesReadable()
    {
        return implode(', ', $this->getRoles());
    }

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
     * Set code
     *
     * @param string $code
     *
     * @return AccessKey
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return AccessKey
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lastUse
     *
     * @param \DateTime $lastUse
     *
     * @return AccessKey
     */
    public function setLastUse($lastUse)
    {
        $this->last_use = $lastUse;

        return $this;
    }

    /**
     * Get lastUse
     *
     * @return \DateTime
     */
    public function getLastUse()
    {
        return $this->last_use;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return AccessKey
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }


    /**
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     *
     * @return AccessKey
     */
    public function setOwner(\AppBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \AppBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
