<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Webmozart\Assert\Assert;

class HeartbeatContext implements Context
{
    private KernelBrowser $client;

    public function __construct(KernelBrowser $client)
    {
        $this->client = $client;
    }

    /**
     * @When I do a heartbeat with guid :guid
     */
    public function iDoAHeartbeatWithGuid(string $guid): void
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
     * @Then I should see that the screen is registered
     */
    public function iShouldSeeThatTheScreenIsRegistered(): void
    {
        Assert::eq($this->client->getInternalResponse()->getStatusCode(), 204);
    }

    /**
     * @Then I should see that the screen is not registered
     */
    public function iShouldSeeThatTheScreenIsNotRegistered(): void
    {
        Assert::eq($this->client->getInternalResponse()->getStatusCode(), 404);
    }

    /**
     * @Then I should see that there is a command :command available
     */
    public function iShouldSeeThatThereIsACommandAvailable(string $command): void
    {
        $response = $this->client->getResponse();
        $data = \json_decode($response->getContent());
        Assert::eq($data->command->command, $command);
    }
}
