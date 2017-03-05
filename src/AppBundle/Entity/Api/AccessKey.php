<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  02.02.17
 * @time     :  19:45
 */

namespace AppBundle\Entity\Api;

use AppBundle\Entity\Interfaces\Ownable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use \AppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * AppBundle\Entity\Api\AccessKey
 *
 * @ORM\Entity
 * @ORM\Table(name="api_access_keys")
 */
class AccessKey
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_use;

    /**
     * @ORM\Column(type="simple_array")
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
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
