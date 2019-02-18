<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Transform;

use App\Entity\Screen;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class ScreenTransformerContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

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
