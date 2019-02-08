<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Api\v1;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use shinage\server\behat\Service\ApiV1ClientContext;
use Webmozart\Assert\Assert;

class FilePoolContext implements Context
{
    /** @var ApiV1ClientContext */
    private $apiV1ClientContext;

    public function __construct(
        ApiV1ClientContext $apiV1ClientContext
    ) {
        $this->apiV1ClientContext = $apiV1ClientContext;
    }

    /**
     * @When /^I get the file pool contents of "([^"]*)"$/
     */
    public function iGetTheFilePoolContentsOf(string $path): void
    {
        $this->apiV1ClientContext->executeRequest('get', 'files' . $path);
    }

    /**
     * @When /^I get the file pool contents of "([^"]*)" if modfied since "([^"]*)"$/
     */
    public function iGetTheFilePoolContentsOfIfModfiedSince(string $path, string $date): void
    {
        $this->apiV1ClientContext->executeRequest(
            'get',
            'files' . $path,
            null,
            ['if-modified-since' => \gmdate('D, d M Y G:i:s T', strtotime($date))]
        );
    }

    /**
     * @Then /^I can see that the api response contains (file|directory) "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseContainsDirectory(string $type, string $content): void
    {
        if ($this->apiV1ClientContext->getResponseStatusCode() < 200 || $this->apiV1ClientContext->getResponseStatusCode() > 299) {
            throw new \Exception('Invalid API response: ' . $this->apiV1ClientContext->getResponseBody());
        }

        if ('directory' === $type && '/' !== substr($content, -1)) {
            $content .= '/';
        }

        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        if (false === \in_array($content, $json)) {
            throw new \Exception(
                'Directory listing content not found. Expected "' . $content . '", found: ' . \var_export($json, true)
            );
        }
    }

    /**
     * @Then /^I can see that the api response does not contain (file|directory) "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseDoesNotContainFile(string $type, string $content): void
    {
        if ($this->apiV1ClientContext->getResponseStatusCode() < 200 || $this->apiV1ClientContext->getResponseStatusCode() > 299) {
            throw new \Exception('Invalid API response: ' . $this->apiV1ClientContext->getResponseBody());
        }

        if ('directory' === $type && '/' !== substr($content, -1)) {
            $content .= '/';
        }

        $json = \json_decode($this->apiV1ClientContext->getResponseBody());
        if (true === \in_array($content, $json)) {
            throw new \Exception(
                'Directory listing content found. Expected NOT to find "' . $content .
                '", but found it: ' . \var_export($json, true)
            );
        }
    }

    /**
     * @When /^I put a file to "([^"]*)" with contents:$/
     */
    public function iPutAFileToWithContents(string $path, PyStringNode $content): void
    {
        $this->apiV1ClientContext->executeRequest('put', 'files' . $path, $content->getRaw());
    }

    /**
     * @When /^I delete at "([^"]*)"$/
     */
    public function iDeleteAt(string $path): void
    {
        $this->apiV1ClientContext->executeRequest('delete', 'files' . $path);
    }

    /**
     * @Then /^I can see that the returned file contains$/
     */
    public function iCanSeeThatTheReturnedFileContains(PyStringNode $string): void
    {
        Assert::contains($this->apiV1ClientContext->getResponseBody(), $string->getRaw());
    }
}
