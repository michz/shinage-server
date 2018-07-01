<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

use AppBundle\Entity\Screen;
use AppBundle\Entity\ScreenAssociation as ScreenAssociationEntity;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScreenAssociation
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

    public function isUserAllowed(Screen $screen, User $user): bool
    {
        return $this->isUserAllowedTo($screen, $user, 'author');
    }

    public function isUserAllowedTo(Screen $screen, User $user, string $attribute): bool
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(['screen' => $screen->getGuid()]);
        $orgas = $user->getOrganizations();

        foreach ($assoc as $a) { /** @var ScreenAssociationEntity $a */
            if ($user === $a->getUser()) {
                return $this->roleGreaterOrEqual($a->getRole(), $attribute);
            }

            foreach ($orgas as $o) { /** @var User $o */
                if ($o === $a->getUser()) {
                    return $this->roleGreaterOrEqual($a->getRole(), $attribute);
                }
            }
        }

        return false;
    }

    private function roleGreaterOrEqual(string $granted, string $reference): bool
    {
        if ('admin' === $granted || $granted === $reference) {
            return true;
        }
        if ('manage' === $granted && 'author' === $reference) {
            return true;
        }
        return false;
    }

    public function isScreenAssociated(Screen $screen): bool
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(['screen' => $screen->getGuid()]);

        return count($assoc) > 0;
    }

    /**
     * @param string $owner (format: "user:<id>" or "orga:<id>")
     * @param string $role  (from ScreenRoleType-enum)
     */
    public function associateByString(Screen $screen, string $owner, string $role): ScreenAssociationEntity
    {
        $assoc = new ScreenAssociationEntity();
        $assoc->setScreen($screen);
        $assoc->setRole($role);

        $aOwner = explode(':', $owner);
        switch ($aOwner[0]) {
            case 'user':
            case 'orga':
                $assoc->setUser($this->em->find('AppBundle:User', $aOwner[1]));
                break;
            default:
                // Error above. Use current user as default.
                // TODO{s:0} Throw error?
                $user = $this->tokenStorage->getToken()->getUser();
                $assoc->setUser($user);
                break;
        }

        $this->em->persist($assoc);
        $this->em->flush();
        return $assoc;
    }
}
