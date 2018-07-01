<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Screen;
use AppBundle\Entity\ScreenAssociation;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ScreenRepository
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Screen[]
     */
    public function getScreensForUser(User $user): array
    {
        $query = $this->em->createQuery(
            'SELECT screen FROM ' . Screen::class . ' screen ' .
            '    JOIN ' . ScreenAssociation::class . ' association ' .
            '    WHERE association.user = :user AND association.screen = screen'
        );
        return $query
                ->setParameter('user', $user)
                ->getResult();
    }
}
