<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\Screen;
use App\Entity\ScreenAssociation as ScreenAssociationEntity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScreenAssociation
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function isUserAllowed(Screen $screen, User $user): bool
    {
        return $this->isUserAllowedTo($screen, $user, 'author');
    }

    public function isUserAllowedTo(Screen $screen, User $user, string $attribute): bool
    {
        $organizations = $user->getOrganizations();

        $users = [$user];
        foreach ($organizations as $organization) {
            $users[] = $organization;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('assoc')
            ->from(\App\Entity\ScreenAssociation::class, 'assoc')
            ->where($queryBuilder->expr()->eq('assoc.screen', ':screen'))
            ->andWhere($queryBuilder->expr()->in('assoc.user', ':users'))
            ->setParameter(':screen', $screen)
            ->setParameter(':users', $users);

        $associations = $queryBuilder->getQuery()->execute();
        /** @var \App\Entity\ScreenAssociation $association */
        foreach ($associations as $association) {
            if (in_array($attribute, $association->getRoles())) {
                return true;
            }
        }

        return false;
    }

    public function isScreenAssociated(Screen $screen): bool
    {
        $rep = $this->entityManager->getRepository('App:ScreenAssociation');
        $assoc = $rep->findBy(['screen' => $screen->getGuid()]);

        return count($assoc) > 0;
    }

    /**
     * @param string   $owner (format: "user:<id>" or "orga:<id>")
     * @param string[] $roles
     *
     * @deprecated
     */
    public function associateByString(Screen $screen, string $owner, array $roles): ScreenAssociationEntity
    {
        $assoc = new ScreenAssociationEntity();
        $assoc->setScreen($screen);
        $assoc->setRoles($roles);

        $aOwner = explode(':', $owner);
        switch ($aOwner[0]) {
            case 'user':
            case 'orga':
                $assoc->setUser($this->entityManager->find('App:User', $aOwner[1]));
                break;
            default:
                // Error above. Use current user as default.
                // TODO{s:0} Throw error?
                $user = $this->tokenStorage->getToken()->getUser();
                $assoc->setUser($user);
                break;
        }

        $this->entityManager->persist($assoc);
        $this->entityManager->flush();
        return $assoc;
    }
}
