<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 28.12.16
 * Time: 19:37
 */


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * AppBundle\Entity\User
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Organization", inversedBy="users")
     * @ORM\JoinTable(name="users_organizations")
     */
    private $organizations;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

}