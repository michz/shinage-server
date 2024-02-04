<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\ScreenRemote;

class PlayablePresentation
{
    public PlayablePresentationSettings $settings;

    /** @var PlayablePresentationSlide[] */
    public array $slides = [];

    public int $lastModified = 0;

    public function __construct()
    {
        $this->settings = new PlayablePresentationSettings();
    }
}
