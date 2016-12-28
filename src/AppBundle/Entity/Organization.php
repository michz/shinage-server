<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 28.12.16
 * Time: 19:37
 */


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Organization
 *
 * @ORM\Entity
 * @ORM\Table(name="organizations")
 */
class Organization {
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="organizations")
     */
    private $users;



}