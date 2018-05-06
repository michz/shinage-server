<?php

namespace shinage\server\behat\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  06.05.18
 * @time     :  15:41
 */

class ScreenContext implements Context
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Given /^There is a screen with guid "([^"]*)"$/
     */
    public function thereIsAScreenWithGuid(string $guid)
    {
        echo "@TODO";
    }
}
