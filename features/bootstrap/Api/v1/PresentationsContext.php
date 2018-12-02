<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Api\v1;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use shinage\server\behat\Service\ApiV1ClientContext;
use Webmozart\Assert\Assert;

class PresentationsContext implements Context
{
    /** @var ApiV1ClientContext */
    private $apiV1ClientContext;

    public function __construct(
        ApiV1ClientContext $apiV1ClientContext
    ) {
        $this->apiV1ClientContext = $apiV1ClientContext;
    }

    /**
     * @When /^I get the list of presentations$/
     */
    public function iGetTheListOfPresentations()
    {
        $this->apiV1ClientContext->executeRequest('get', 'presentations');
    }

    /**
     * @When /^I get the presentation "([^"]*)"$/
     */
    public function iGetThePresentation(string $title)
    {
        $this->apiV1ClientContext->executeRequest('get', 'presentations/' . $title);
    }

    /**
     * @When /^I update the presentation "([^"]*)" with settings:$/
     */
    public function iUpdateThePresentationWithSettings(string $title, PyStringNode $string)
    {
        $this->apiV1ClientContext->executeRequest('post', 'presentations/' . $title, $string->getRaw());
    }

    /**
     * @Given /^I can see that the api response contains no presentation$/
     */
    public function iCanSeeThatTheApiResponseContainsNoPresentation()
    {
        Assert::eq($this->apiV1ClientContext->getResponseBody(), '[]');
    }

    /**
     * @Given /^I can see that the api response contains a presentation with name "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseContainsAPresentationWithName(string $title)
    {
        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        foreach ($json as $presentation) {
            if ($presentation->title === $title) {
                return;
            }
        }

        throw new \Exception('Desired presentation not found: ' . $title);
    }
}
