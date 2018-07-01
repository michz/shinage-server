<?php

namespace shinage\server\behat\Setup;

use AppBundle\Entity\Screen;
use AppBundle\Entity\ScreenAssociation;
use AppBundle\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  06.05.18
 * @time     :  15:41
 */

class ScreenContext implements Context
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Given /^There is a screen with guid "([^"]*)"$/
     */
    public function thereIsAScreenWithGuid(string $guid)
    {
        $screen = new Screen();
        $screen->setGuid($guid);
        $screen->setName('Screen ' . $guid);
        $screen->setFirstConnect(new \DateTime());
        $screen->setLastConnect(new \DateTime());
        $screen->setLocation('Somewhere');
        $this->entityManager->persist($screen);

        $user = new User();
        $user->setName('Owner of ' . $guid);
        $user->setEmail('default-owner@test.test');
        $user->setPassword('testpasswordunusable');
        $this->entityManager->persist($user);

        $this->entityManager->flush();

        $association = new ScreenAssociation();
        $association->setRole('admin');
        $association->setUser($user);
        $association->setScreen($screen);
        $this->entityManager->persist($association);

        $this->entityManager->flush();
    }
}
