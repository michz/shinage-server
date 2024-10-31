<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Hook;

use App\Service\MailerTestTransport;
use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

/*
 * Inspired by Sylius ( see https://sylius.com ).
 */
class PurgeContext implements Context
{
    private EntityManagerInterface $entityManager;

    private MailerTestTransport $mailerTestTransport;

    /** @var array|string[] */
    private array $poolFiles = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerTestTransport $mailerTestTransport
    ) {
        $this->entityManager = $entityManager;
        $this->mailerTestTransport = $mailerTestTransport;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase(): void
    {
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
        $this->mailerTestTransport->reset();
    }
}
