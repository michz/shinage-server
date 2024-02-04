<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Transform;

use App\Entity\Screen;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class ScreenTransformerContext implements Context
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Transform :screen
     * @Transform /^screen "([^"]+)"$/
     * @Transform /^The screen "([^"]+)"$/
     * @Transform /^the screen "([^"]+)"$/
     */
    public function getScreen(string $guid): Screen
    {
        return $this->entityManager->find(Screen::class, $guid);
    }
}
