<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use shinage\server\behat\Service\SharedStorage;

class PresentationsContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SharedStorage */
    private $sharedStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        SharedStorage $sharedStorage
    ) {
        $this->entityManager = $entityManager;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given /^The user "([^"]*)" has a presentation of type "([^"]*)" and title "([^"]*)"$/
     */
    public function theUserHasAPresentationOfTypeAndName(string $username, string $type, string $name): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        $presentation = new Presentation();
        $presentation->setLastModified(new \DateTime());
        $presentation->setOwner($user);
        $presentation->setTitle($name);
        $presentation->setType($type);

        $this->entityManager->persist($presentation);
        $this->entityManager->flush();

        $this->sharedStorage->set('presentation', $presentation);
    }

    /**
     * @Given /^There is a presentation of type "([^"]*)" called "([^"]*)"$/
     */
    public function thereIsAPresentationOfTypeCalled(string $type, string $name): void
    {
        $presentation = new Presentation();
        $presentation->setLastModified(new \DateTime());
        //$presentation->setOwner();
        $presentation->setTitle($name);
        $presentation->setType($type);

        $this->entityManager->persist($presentation);
        $this->entityManager->flush();

        $this->sharedStorage->set('presentation', $presentation);
    }

    // Given /^(The presentation "([^"]+)") is scheduled now for (screen "([^"]+)")$/

    /**
     * @Given The presentation :presentation is scheduled now for screen :screen
     */
    public function thePresentationIsScheduledNowForScreen(Presentation $presentation, Screen $screen): void
    {
        $start = new \DateTime('now');
        $start->sub(new \DateInterval('PT1H'));
        $end = new \DateTime('now');
        $end->add(new \DateInterval('PT1H'));

        $scheduledPresentation = new ScheduledPresentation();
        $scheduledPresentation->setPresentation($presentation);
        $scheduledPresentation->setScreen($screen);
        $scheduledPresentation->setScheduledStart($start);
        $scheduledPresentation->setScheduledEnd($end);

        $this->entityManager->persist($scheduledPresentation);
        $this->entityManager->flush();
    }
}
