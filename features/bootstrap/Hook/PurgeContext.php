<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Hook;

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

/*
 * Inspired by Sylius ( see https://sylius.com ).
 */
class PurgeContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array|string[] */
    private $poolFiles = [];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
    }

    /**
     * @AfterScenario
     */
    public function purgePool(): void
    {
        $folders = [];
        foreach ($this->poolFiles as $poolFile) {
            if (\is_file($poolFile)) {
                @unlink($poolFile);
            } elseif (\is_dir($poolFile)) {
                $folders[] = $poolFile;
            }
        }

        $this->purgePoolFolders($folders);
    }

    /**
     * @param array|string[] $folders
     */
    private function purgePoolFolders(array $folders): void
    {
        foreach ($folders as $folder) {
            @rmdir($folder);
            // @TODO recursive
        }
    }

    public function addPurgablePoolFile(string $path): void
    {
        $this->poolFiles[] = $path;
    }
}
