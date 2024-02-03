<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\ScreenRemote;

class PlayablePresentationSlide
{
    public string $type = 'Image';

    public string $title = '';

    public int $duration = 5000;

    public string $transition = '';

    public string $src = '';
}
