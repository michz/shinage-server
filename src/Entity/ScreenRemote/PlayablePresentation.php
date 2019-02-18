<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\ScreenRemote;

class PlayablePresentation
{
    /** @var PlayablePresentationSettings */
    public $settings;

    /** @var PlayablePresentationSlide[]|array */
    public $slides = [];

    /** @var int */
    public $lastModified = 0;

    public function __construct()
    {
        $this->settings = new PlayablePresentationSettings();
    }
}
