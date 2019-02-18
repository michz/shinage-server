<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\ScreenRemote;

class PlayablePresentationSlide
{
    /** @var string */
    public $type        = 'Image';

    /** @var string */
    public $title       = '';

    /** @var int */
    public $duration    = 5000;

    /** @var string */
    public $transition  = '';

    /** @var string */
    public $src         = '';
}
