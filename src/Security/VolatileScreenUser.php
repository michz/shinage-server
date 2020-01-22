<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use App\Entity\Screen;

class VolatileScreenUser
{
    /** @var Screen */
    private $screen;

    public function __construct(Screen $screen)
    {
        $this->screen = $screen;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function __toString(): string
    {
        return $this->screen->getGuid();
    }
}
