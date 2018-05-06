<?php
declare(strict_types=1);

namespace shinage\server\behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Doctrine\ORM\EntityManagerInterface;

class HeartbeatContext implements Context
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * HeartbeatContext constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @When /^I do a heartbeat with guid "([^"]*)"$/
     */
    public function iDoAHeartbeatWithGuid(string $guid)
    {
        // @TODO Do API call.
        throw new PendingException();
    }

    /**
     * @Then /^I should see that the screen is not registered$/
     */
    public function iShouldSeeThatTheScreenIsNotRegistered()
    {
        // @TODO Check API call response
        throw new PendingException();
    }
}
