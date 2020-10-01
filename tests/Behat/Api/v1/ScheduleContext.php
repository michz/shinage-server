<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Api\v1;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Behat\Client\ApiV1ClientContext;
use Webmozart\Assert\Assert;

class ScheduleContext implements Context
{
    /** @var ApiV1ClientContext */
    private $apiV1ClientContext;

    /** @var \stdClass */
    private $rememberedItem;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ApiV1ClientContext $apiV1ClientContext,
        EntityManagerInterface $entityManager
    ) {
        $this->apiV1ClientContext = $apiV1ClientContext;
        $this->entityManager = $entityManager;
    }

    /**
     * @When I get the schedule
     */
    public function iGetTheSchedule(): void
    {
        $this->apiV1ClientContext->executeRequest('get', 'schedule');
    }

    /**
     * @When I get the schedule from :from
     */
    public function iGetTheScheduleFrom(string $from): void
    {
        $this->apiV1ClientContext->executeRequest(
            'get',
            'schedule?from=' . $from
        );
    }

    /**
     * @When I get the schedule until :until
     */
    public function iGetTheScheduleUntil(string $until): void
    {
        $this->apiV1ClientContext->executeRequest(
            'get',
            'schedule?until=' . $until
        );
    }

    /**
     * @When I get the schedule from :from until :until
     */
    public function iGetTheScheduleFromUntil(string $from, string $until): void
    {
        $this->apiV1ClientContext->executeRequest(
            'get',
            'schedule?from=' . $from . '&until=' . $until
        );
    }

    /**
     * @When I schedule the presentation :presentation on screen :screen from :from to :to
     */
    public function iScheduleThePresentationOnScreenFromTo(
        Presentation $presentation,
        Screen $screen,
        string $from,
        string $to
    ): void {
        $this->apiV1ClientContext->executeRequest('put', 'schedule', '{
            "presentation": ' . $presentation->getId() . ',
            "screen": "' . $screen->getGuid() . '",
            "start": "' . $from . '",
            "end": "' . $to . '"
        }');
    }

    /**
     * @When /^I remember the first item of the schedule$/
     */
    public function iRememberTheFirstItemOfTheSchedule(): void
    {
        $schedule = \json_decode($this->apiV1ClientContext->getResponseBody());
        $this->rememberedItem = $schedule[0];
    }

    /**
     * @When I delete the remembered item of the schedule
     */
    public function iDeleteTheRememberedItemOfTheSchedule(): void
    {
        $this->apiV1ClientContext->executeRequest('delete', 'schedule/' . $this->rememberedItem->id);
    }

    /**
     * @When I delete the presentation :presentation from screen :screen
     */
    public function iDeleteThePresentationFromScreen(Presentation $presentation, Screen $screen): void
    {
        $repo = $this->entityManager->getRepository(ScheduledPresentation::class);
        $scheduledPresentation = $repo->findOneBy(['presentation' => $presentation, 'screen' => $screen]);
        $this->apiV1ClientContext->executeRequest('delete', 'schedule/' . $scheduledPresentation->getId());
    }

    /**
     * @Then I can see that the schedule is empty
     */
    public function iCanSeeTheScheduleIsEmpty(): void
    {
        if ($this->apiV1ClientContext->getResponseStatusCode() < 200 || $this->apiV1ClientContext->getResponseStatusCode() > 299) {
            throw new \Exception('Invalid API response: ' . $this->apiV1ClientContext->getResponseBody());
        }

        $schedule = \json_decode($this->apiV1ClientContext->getResponseBody());
        Assert::eq($schedule, [], 'The schedule is not empty.');
    }

    /**
     * @Then I should see that the schedule contains exactly :count item
     * @Then I should see that the schedule contains exactly :count items
     */
    public function iShouldSeeThatTheScheduleContainsExactlyItem(int $count): void
    {
        $schedule = \json_decode($this->apiV1ClientContext->getResponseBody());
        Assert::eq(\count($schedule), $count, 'The number of scheduled items (%s) does not match the expected (%2$s).');
    }

    /**
     * @Then I should see the presentation :presentation scheduled on screen :screen from :from to :to
     */
    public function iShouldSeeThePresentationScheduledOnScreenFromTo(
        Presentation $presentation,
        Screen $screen,
        string $from,
        string $to
    ): void {
        $schedule = \json_decode($this->apiV1ClientContext->getResponseBody());

        foreach ($schedule as $item) {
            if ($item->presentation === $presentation->getId() && $item->screen === $screen->getGuid() && $item->start === $from && $item->end === $to) {
                // Match! So return gracefully.
                return;
            }
        }

        // Not found, throw exception
        throw new \ErrorException('Expected scheduled item not found.');
    }

    /**
     * @Then I should see in the response that the presentation :presentation is scheduled on screen :screen from :from to :to
     */
    public function iShouldSeeInTheResponseThatThePresentationIsScheduledOnScreenFromTo(
        Presentation $presentation,
        Screen $screen,
        string $from,
        string $to
    ): void {
        $schedule = \json_decode($this->apiV1ClientContext->getResponseBody());

        Assert::eq($schedule->presentation, $presentation->getId());
        Assert::eq($schedule->screen, $screen->getGuid());
        Assert::eq($schedule->start, $from);
        Assert::eq($schedule->end, $to);
    }
}
