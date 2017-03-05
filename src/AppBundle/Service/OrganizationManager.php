<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class OrganizationManager
{
    protected $em = null;
    protected $tokenStorage = null;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getUsers(User $orga)
    {
        //@TODO{s:5} Liste der User abrufen
        return [];
    }
}
