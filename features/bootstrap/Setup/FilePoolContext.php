<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use AppBundle\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use shinage\server\behat\Helper\StringInflector;
use shinage\server\behat\Service\SharedStorage;

class FilePoolContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SharedStorage */
    private $sharedStorage;

    /** @var string */
    private $basePath;

    public function __construct(
        EntityManagerInterface $entityManager,
        SharedStorage $sharedStorage,
        string $basePath
    ) {
        $this->entityManager = $entityManager;
        $this->sharedStorage = $sharedStorage;
        $this->basePath = $basePath;
    }

    /**
     * @Given /^In the pool the user "([^"]*)" has a file "([^"]*)" with content "([^"]*)"$/
     */
    public function inThePoolTheUserHasAFileWithContent(string $userName, string $name, string $content)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);
        $id = $user->getId();

        $fullPath = $this->basePath . '/user-' . $id  . '/' . $name;
        $path = dirname($fullPath);
        if (false === is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($fullPath, $content);
        $this->sharedStorage->set(StringInflector::nameToCode('pool-file'), $fullPath);
    }

    /**
     * @Given /^(this pool file) has the last modified timestamp "([^"]*)"$/
     */
    public function thisFileHasTheLastModifiedTimestamp(string $poolFile, string $date)
    {
        touch($poolFile, strtotime($date));
    }
}
