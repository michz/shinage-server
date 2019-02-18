<?php
declare(strict_types=1);

/*
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
    public function iGetTheListOfPresentations(): void
    {
        $this->apiV1ClientContext->executeRequest('get', 'presentations');
    }

    /**
     * @When /^I get the presentation "([^"]*)"$/
     */
    public function iGetThePresentation(string $title): void
    {
        $this->apiV1ClientContext->executeRequest(
            'get',
            'presentations/' . $this->getPresentationByTitle($title)->getId()
        );
    }

    /**
     * @When /^I get the presentation with id (\d+)$/
     */
    public function iGetThePresentationWithId(int $id): void
    {
        $this->apiV1ClientContext->executeRequest('get', 'presentations/' . $id);
    }

    /**
     * @When /^I update the presentation "([^"]*)" with settings:$/
     */
    public function iUpdateThePresentationWithSettings(string $title, PyStringNode $string): void
    {
        $presentation = $this->getPresentationByTitle($title);
        $object = new \stdClass();

        $object->id = $presentation->getId();
        $object->notes = $presentation->getNotes();
        $object->title = $presentation->getTitle();
        $object->settings = $string->getRaw();

        $this->apiV1ClientContext->executeRequest('put', 'presentations', \json_encode($object));
    }

    /**
     * @When /^I delete the presentation "([^"]*)"$/
     */
    public function iDeleteThePresentation(string $title): void
    {
        $this->apiV1ClientContext->executeRequest(
            'delete',
            'presentations/' . $this->getPresentationByTitle($title)->getId()
        );
    }

    /**
     * @Given /^I can see that the api response contains no presentation$/
     */
    public function iCanSeeThatTheApiResponseContainsNoPresentation(): void
    {
        Assert::eq($this->apiV1ClientContext->getResponseBody(), '[]');
    }

    /**
     * @Given /^I can see that the api response contains a presentation with name "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseContainsAPresentationWithName(string $title): void
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
     * @Then /^I can see that the api response does not contain a presentation with name "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseDoesNotContainAPresentationWithName(string $title): void
    {
        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        foreach ($json as $presentation) {
            if ($presentation->title === $title) {
                throw new \Exception('Presentation found, that should not be there: ' . $title);
            }
        }
    }

    /**
     * @Given /^I can see that the presentation contains a slide with title "([^"]*)"$/
     */
    public function iCanSeeThatThisPresentationContainsASlideWithTitle(string $slideTitle): void
    {
        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        $settings = \json_decode($json->settings);
        foreach ($settings->slides as $slide) {
            if ($slide->title === $slideTitle) {
                return;
            }
        }

        throw new \Exception('Slide with title `' . $slideTitle . '` not found.');
    }
}
