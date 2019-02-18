<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Setup;

use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use shinage\server\behat\Helper\StringInflector;
use shinage\server\behat\Hook\PurgeContext;
use shinage\server\behat\Service\SharedStorage;

class FilePoolContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SharedStorage */
    private $sharedStorage;

    /** @var PurgeContext */
    private $purgeContext;

    /** @var string */
    private $basePath;

    public function __construct(
        EntityManagerInterface $entityManager,
        SharedStorage $sharedStorage,
        PurgeContext $purgeContext,
        string $basePath
    ) {
        $this->entityManager = $entityManager;
        $this->sharedStorage = $sharedStorage;
        $this->purgeContext = $purgeContext;
        $this->basePath = $basePath;
    }

    /**
     * @Given /^In the pool the user "([^"]*)" has a file "([^"]*)" with content "([^"]*)"$/
     */
    public function inThePoolTheUserHasAFileWithContent(string $userName, string $name, string $content): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);
        $id = $user->getId();

        $fullPath = $this->basePath . '/user-' . $id . '/' . $name;
        $path = dirname($fullPath);
        $this->createFolderRecursively($path);

        file_put_contents($fullPath, $content);
        $this->sharedStorage->set(StringInflector::nameToCode('pool-file'), $fullPath);
        $this->purgeContext->addPurgablePoolFile($fullPath);
    }

    /**
     * @Given /^(this pool file) has the last modified timestamp "([^"]*)"$/
     */
    public function thisFileHasTheLastModifiedTimestamp(string $poolFile, string $date): void
    {
        touch($poolFile, strtotime($date));
    }

    private function createFolderRecursively(string $folderPath): void
    {
        if (\is_dir($folderPath)) {
            return;
        }

        $parent = dirname($folderPath);

        // create parent folder
        $this->createFolderRecursively($parent);

        mkdir($folderPath, 0777, false);
        $this->purgeContext->addPurgablePoolFile($folderPath);
    }
}
