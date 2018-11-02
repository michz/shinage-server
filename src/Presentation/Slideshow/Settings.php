<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow;

use App\Presentation\Slideshow\Slides\Slide;
use JMS\Serializer\Annotation as JMS;

class Settings
{
    /**
     * @var Slide[]|array
     *
     * @JMS\Type("array<App\Presentation\Slideshow\Slides\Slide>")
     */
    protected $slides = [];

    /**
     * @return Slide[]|array
     */
    public function getSlides(): array
    {
        return $this->slides;
    }

    /**
     * @param Slide[]|array $slides
     */
    public function setSlides(array $slides): self
    {
        $this->slides = $slides;
        return $this;
    }
}
