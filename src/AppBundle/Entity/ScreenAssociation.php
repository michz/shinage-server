<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:21
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;


/**
 * AppBundle\Entity\ScreenAssociation
 *
 * @ORM\Entity
 * @ORM\Table(name="screen_associations")
 */

class ScreenAssociation {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Screen")
     * @ORM\JoinColumn(name="screen_id", referencedColumnName="guid", nullable=false)
     */
    protected $screen;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="orga_id", referencedColumnName="id", nullable=true)
     */
    protected $orga_id;

    /**
     * @ORM\Column(type="enumscreenrole")
     */
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
     * @param enumscreenrole $role
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
     * @return enumscreenrole
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

    /**
     * Set orgaId
     *
     * @param \AppBundle\Entity\Organization $orgaId
     *
     * @return ScreenAssociation
     */
    public function setOrgaId(\AppBundle\Entity\Organization $orgaId = null)
    {
        $this->orga_id = $orgaId;

        return $this;
    }

    /**
     * Get orgaId
     *
     * @return \AppBundle\Entity\Organization
     */
    public function getOrgaId()
    {
        return $this->orga_id;
    }
}
