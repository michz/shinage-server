<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Api\v1;

use App\Entity\Presentation;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use shinage\server\behat\Service\ApiV1ClientContext;
use Webmozart\Assert\Assert;

class PresentationsContext implements Context
{
    /** @var ApiV1ClientContext */
    private $apiV1ClientContext;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ApiV1ClientContext $apiV1ClientContext,
        EntityManagerInterface $entityManager
    ) {
        $this->apiV1ClientContext = $apiV1ClientContext;
        $this->entityManager = $entityManager;
    }

    private function getPresentationByTitle(string $title): Presentation
    {
        $repo = $this->entityManager->getRepository(Presentation::class);
        return $repo->findOneBy(['title' => $title]);
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
        $this->apiV1ClientContext->executeRequest(
            'get',
            'presentations/' . $this->getPresentationByTitle($title)->getId()
        );
    }

    /**
     * @When /^I update the presentation "([^"]*)" with settings:$/
     */
    public function iUpdateThePresentationWithSettings(string $title, PyStringNode $string)
    {
        $this->apiV1ClientContext->executeRequest(
            'post',
            'presentations/' . $this->getPresentationByTitle($title)->getId(),
            $string->getRaw()
        );
    }

    /**
     * @When /^I delete the presentation "([^"]*)"$/
     */
    public function iDeleteThePresentation(string $title)
    {
        $this->apiV1ClientContext->executeRequest(
            'delete',
            'presentations/' . $this->getPresentationByTitle($title)->getId()
        );
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

    /**
     * @Given /^I can see that the presentation contains a slide with title "([^"]*)"$/
     */
    public function iCanSeeThatThisPresentationContainsASlideWithTitle(string $slideTitle)
    {
        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        foreach ($json->slides as $slide) {
            if ($slide->title === $slideTitle) {
                return;
            }
        }

        throw new \Exception('Slide with title `' . $slideTitle . '` not found.');
    }
}
