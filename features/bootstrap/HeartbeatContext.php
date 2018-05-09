<?php
declare(strict_types=1);

namespace shinage\server\behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Webmozart\Assert\Assert;

class HeartbeatContext implements Context
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $client;

    public function __construct(EntityManagerInterface $entityManager, Client $client)
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    /**
     * @When /^I do a heartbeat with guid "([^"]*)"$/
     */
    public function iDoAHeartbeatWithGuid(string $guid)
    {
        $this->client->request(
            'GET',
            '/screen-remote/heartbeat/' . $guid,
            [],
            [],
            ['ACCEPT' => 'application/json']
        );
    }

    /**
     * @Then /^I should see that the screen is registered$/
     */
    public function iShouldSeeThatTheScreenIsRegistered()
    {
        /** @var Response $response */
        $response = $this->client->getResponse();
        $data = \json_decode($response->getContent());

        $this->assertScreenState('registered', $data->screen_status);
    }

    /**
     * @Then /^I should see that the screen is not registered$/
     */
    public function iShouldSeeThatTheScreenIsNotRegistered()
    {
        /** @var Response $response */
        $response = $this->client->getResponse();
        $data = \json_decode($response->getContent());

        $this->assertScreenState('not_registered', $data->screen_status);
    }

    private function assertScreenState(string $expected, string $actual)
    {
        Assert::eq(
            $expected,
            $actual,
            'Wrong screen status returned: ' . $actual
        );
    }
}
