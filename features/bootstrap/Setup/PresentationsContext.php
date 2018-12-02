<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use App\Entity\Presentation;
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
     * @Given /^The user "([^"]*)" has a presentation of type "([^"]*)" and name "([^"]*)"$/
     */
    public function theUserHasAPresentationOfTypeAndName(string $username, string $type, string $name)
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
}
