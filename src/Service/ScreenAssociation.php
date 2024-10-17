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

readonly class ScreenAssociation
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
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
            if (\in_array($attribute, $association->getRoles())) {
                return true;
            }
        }

        return false;
    }

    public function isScreenAssociated(Screen $screen): bool
    {
        $rep = $this->entityManager->getRepository(\App\Entity\ScreenAssociation::class);
        $assoc = $rep->findBy(['screen' => $screen->getGuid()]);

        return \count($assoc) > 0;
    }

    /**
     * @param string[] $roles
     *
     * @deprecated
     */
    public function associateByString(Screen $screen, int $owner, array $roles): ScreenAssociationEntity
    {
        $assoc = new ScreenAssociationEntity();
        $assoc->setScreen($screen);
        $assoc->setRoles($roles);
        $assoc->setUser($this->entityManager->find(User::class, $owner));

        $this->entityManager->persist($assoc);
        $this->entityManager->flush();
        return $assoc;
    }
}
