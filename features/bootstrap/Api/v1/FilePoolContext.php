<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Api\v1;

use Behat\Behat\Context\Context;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class FilePoolContext implements Context
{
    /** @var string */
    private $apiKey = '';

    /** @var null|Response */
    private $response = null;

    /**
     * @Given /^I use the api key "([^"]*)"$/
     */
    public function iUseTheApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @When /^I get the file pool contents of "([^"]*)"$/
     */
    public function iGetTheFilePoolContentsOf(string $path)
    {
        $client = new Client();
        $this->response = $client->request(
            'get',
            [
                'headers' => [
                    'x-api-token' => $this->apiKey,
                ],
            ]
        );

        $statusCode = $this->response->getStatusCode();
        if ($statusCode < 200 || $statusCode > 299) {
            throw new \Exception('Invalid API response.');
        }
    }

    /**
     * @Then /^I can see that the api response contains (file|directory) "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseContainsDirectory(string $content)
    {
        $json = json_decode($this->response->getBody()->getContents());
        if (false === \in_array($content, $json)) {
            throw new \Exception('Directory listing content not found.');
        }
    }
}
