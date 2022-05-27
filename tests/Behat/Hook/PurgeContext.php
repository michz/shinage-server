<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Hook;

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/*
 * Inspired by Sylius ( see https://sylius.com ).
 */
class PurgeContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array|string[] */
    private $poolFiles = [];

    /** @var string */
    private $mailSpoolPath;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $mailSpoolPath
    ) {
        $this->entityManager = $entityManager;
        $this->mailSpoolPath = $mailSpoolPath;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase(): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
        $this->entityManager->clear();
        $this->purgeMailSpool();
    }

    /**
     * @AfterScenario
     */
    public function purgePool(): void
    {
        $folders = [];
        foreach ($this->poolFiles as $poolFile) {
            if (\is_file($poolFile)) {
                @\unlink($poolFile);
            } elseif (\is_dir($poolFile)) {
                $folders[] = $poolFile;
            }
        }

        $this->purgePoolFolders($folders);
        $this->purgeMailSpool();
    }

    /**
     * @param array|string[] $folders
     */
    private function purgePoolFolders(array $folders): void
    {
        foreach ($folders as $folder) {
            @\rmdir($folder);
            // @TODO recursive
        }
    }

    public function addPurgablePoolFile(string $path): void
    {
        $this->poolFiles[] = $path;
    }

    public function purgeMailSpool(): void
    {
        $filesystem = new Filesystem();
        $finder = $this->getSpooledEmails();

        /** @var File $file */
        foreach ($finder as $file) {
            $filesystem->remove($file->getRealPath());
        }
    }

    public function getSpooledEmails(): Finder
    {
        $finder = new Finder();
        $finder->files()->in($this->mailSpoolPath);
        return $finder;
    }
}
