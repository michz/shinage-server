<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Api\v1;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Webmozart\Assert\Assert;

class FilePoolContext implements Context
{
    /** @var string */
    private $apiKey = '';

    /** @var null|Response */
    private $response = null;

    /** @var string */
    private $rawResponse = '';

    /** @var int */
    private $responseStatusCode = 0;

    /** @var string */
    private $baseUrl;

    public function __construct(
        string $baseUrl
    ) {
        $this->baseUrl = $baseUrl;
    }

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
            $this->baseUrl . '/api/v1/files' . $path,
            [
                'headers' => [
                    'x-api-token' => $this->apiKey,
                ],
                'http_errors' => false,
            ]
        );

        $this->responseStatusCode = $this->response->getStatusCode();
        $this->rawResponse = $this->response->getBody()->getContents();
    }

    /**
     * @Then /^I can see that the api response contains (file|directory) "([^"]*)"$/
     */
    public function iCanSeeThatTheApiResponseContainsDirectory(string $type, string $content)
    {
        if ($this->responseStatusCode < 200 || $this->responseStatusCode > 299) {
            throw new \Exception('Invalid API response: ' . $this->rawResponse);
        }

        if ('directory' === $type && '/' !== substr($content, -1)) {
            $content .= '/';
        }

        $json = \json_decode($this->rawResponse);
        if (false === \in_array($content, $json)) {
            throw new \Exception(
                'Directory listing content not found. Expected "' . $content . '", found: ' . \var_export($json, true)
            );
        }
    }

    /**
     * @Then /^I can see that the api request was successfull$/
     */
    public function iCanSeeThatTheApiRequestWasSuccessfull()
    {
        Assert::notNull($this->response);
        Assert::eq($this->response->getStatusCode(), 200);
    }

    /**
     * @Then /^I get an Access Denied response$/
     */
    public function iGetAnAccessDeniedResponse()
    {
        Assert::notNull($this->response);
        Assert::eq($this->responseStatusCode, 403);
    }

    /**
     * @Then /^I get an Not Found response$/
     */
    public function iGetAnInvalidArgumentExceptionResponse()
    {
        Assert::notNull($this->response);
        Assert::eq($this->responseStatusCode, 404);
        $json = \json_decode($this->rawResponse, true);
        Assert::eq($json['type'], 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException');
    }

    /**
     * @When /^I put a file to "([^"]*)" with contents:$/
     */
    public function iPutAFileToWithContents(string $path, PyStringNode $content)
    {

        $client = new Client();
        $this->response = $client->request(
            'put',
            $this->baseUrl . '/api/v1/files' . $path,
            [
                'headers' => [
                    'x-api-token' => $this->apiKey,
                ],
                'http_errors' => false,
                'body' => $content->getRaw(),
            ]
        );

        $this->responseStatusCode = $this->response->getStatusCode();
        $this->rawResponse = $this->response->getBody()->getContents();
    }

    /**
     * @Then /^I get an No Content response$/
     */
    public function iGetAnNoContentResponse()
    {
        Assert::notNull($this->response);
        Assert::eq($this->responseStatusCode, 204);
    }

    /**
     * @Then /^I get an Bad Request response$/
     */
    public function iGetAnBadRequestResponse()
    {
        Assert::notNull($this->response);
        Assert::eq($this->responseStatusCode, 400);
    }

    /**
     * @Then /^I can see that the returned file contains$/
     */
    public function iCanSeeThatTheReturnedFileContains(PyStringNode $string)
    {
        Assert::contains($this->rawResponse, $string->getRaw());
    }
}
