<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use Behat\Behat\Context\Context;

class FilePoolContext implements Context
{
    /** @var string */
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @Given /^In the pool there is a file "([^"]*)" with content "([^"]*)"$/
     */
    public function inThePoolThereIsAFileWithContent(string $name, string $content)
    {

    }
}
