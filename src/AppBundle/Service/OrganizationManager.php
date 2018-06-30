<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationManager
{
    /** @var EntityManagerInterface|null */
    protected $em = null;

    /** @var TokenStorageInterface|null */
    protected $tokenStorage = null;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return User[]|array
     */
    public function getUsers(User $orga): array
    {
        //$repo = $this->em->getRepository('AppBundle:User');
        //return $repo->findBy(['organization' => $orga]);
        return [];
    }
}
