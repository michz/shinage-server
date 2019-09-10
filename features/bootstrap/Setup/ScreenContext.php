<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use App\Entity\Screen;
use App\Entity\ScreenAssociation;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class ScreenContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Given There is a screen with guid :guid
     */
    public function thereIsAScreenWithGuid(string $guid): void
    {
        $screen = new Screen();
        $screen->setGuid($guid);
        $screen->setName('Screen ' . $guid);
        $screen->setFirstConnect(new \DateTime());
        $screen->setLastConnect(new \DateTime('@0'));
        $screen->setLocation('Somewhere');
        $this->entityManager->persist($screen);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen belongs to an arbitrary user
     */
    public function theScreenBelongsToAnArbitraryUser(Screen $screen): void
    {
        $user = new User();
        $user->setName('Owner of ' . $screen->getGuid());
        $user->setEmail('default-owner@test.test');
        $user->setPassword('testpasswordunusable');
        $this->entityManager->persist($user);

        $this->entityManager->flush();

        $association = new ScreenAssociation();
        $association->setRoles(['view_screenshot', 'manage', 'schedule']);
        $association->setUser($user);
        $association->setScreen($screen);
        $this->entityManager->persist($association);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen belongs to user :user
     */
    public function theScreenBelongsToUser(Screen $screen, User $user): void
    {
        $association = new ScreenAssociation();
        $association->setRoles(['view_screenshot', 'manage', 'schedule']);
        $association->setUser($user);
        $association->setScreen($screen);
        $this->entityManager->persist($association);
        $this->entityManager->flush();
    }

    /**
     * @Given The user :user has the right to :role for the screen :screen
     * @Given The organization :user has the right to :role for the screen :screen
     */
    public function theUserHasTheRightToForTheScreen(Screen $screen, string $role, User $user): void
    {
        $repo = $this->entityManager->getRepository(ScreenAssociation::class);
        $association = $repo->findOneBy(['user' => $user, 'screen' => $screen]);

        if (null === $association) {
            $association = new ScreenAssociation();
            $this->entityManager->persist($association);
            $association->setRoles([]);
            $association->setUser($user);
            $association->setScreen($screen);
        }

        $associationRoles = $association->getRoles();
        if (false === \in_array($role, $associationRoles)) {
            $associationRoles[] = $role;
        }

        $association->setRoles($associationRoles);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen has alarming disabled
     */
    public function theScreenHasAlarmingDisabled(Screen $screen)
    {
        $screen->setAlarmingEnabled(false);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen has alarming enabled
     */
    public function theScreenHasAlarmingEnabled(Screen $screen)
    {
        $screen->setAlarmingEnabled(true);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen has the last connection alarming threshold set to :threshold minutes
     */
    public function theScreenHasTheLastConnectionAlarmingThresholdSetToMinutes(Screen $screen, int $threshold)
    {
        $screen->setAlarmingConnectionThreshold($threshold);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen has last connected :ago minutes ago
     */
    public function theScreenHasLastConnectedMinutesAgo(Screen $screen, int $ago)
    {
        $lastConnect = new \DateTime();
        $lastConnect->sub(new \DateInterval('PT' . $ago . 'M'));
        $screen->setLastConnect($lastConnect);
        $this->entityManager->flush();
    }

    /**
     * @Given The screen :screen has the alarming mail address set to :target
     */
    public function theScreenHasTheAlarmingMailAddressSetTo(Screen $screen, string $target)
    {
        $screen->setAlarmingMailTargets($target);
        $this->entityManager->flush();
    }
}
