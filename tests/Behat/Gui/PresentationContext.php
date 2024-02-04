<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Gui;

use App\Entity\Presentation;
use App\Entity\Screen;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Webmozart\Assert\Assert;

class PresentationContext implements Context
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    public function __construct(EntityManagerInterface $entityManager, KernelBrowser $client)
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    /**
     * @When /^I ask for the current presentation of (screen "([^"]*)")$/
     */
    public function iAskForTheCurrentPresentationOfScreen(Screen $screen): void
    {
        $this->client->request(
            'GET',
            '/screen-remote/current-for/' . $screen->getGuid()
        );
    }

    /**
     * @Then /^I should see the url of (presentation "([^"]+)")$/
     */
    public function iShouldSeeTheUrlOfPresentation(Presentation $presentation): void
    {
        $content = $this->client->getResponse()->getContent();
        Assert::contains($content, '/presentations/' . $presentation->getId());
    }

    /**
     * @Then /^I should see the url of splash presentation with connect code of (screen "([^"]*)")$/
     */
    public function iShouldSeeTheUrlOfSplashPresentationWithConnectCode(Screen $screen): void
    {
        $content = $this->client->getResponse()->getContent();
        Assert::contains($content, '/presentations/0');
        Assert::contains($content, 'connect_code=' . $screen->getConnectCode());
    }

    /**
     * @Given /^I the last connect timestamp of (screen "([^"]*)") is now$/
     */
    public function iTheLastConnectTimestampOfScreenIsNow(Screen $screen): void
    {
        // reload entity as it has been changed
        $this->entityManager->refresh($screen);

        $diff = \abs((new \DateTime())->getTimestamp() - $screen->getLastConnect()->getTimestamp());
        Assert::lessThan($diff, 5);
    }
}
