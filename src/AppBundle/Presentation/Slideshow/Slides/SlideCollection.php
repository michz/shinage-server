<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Presentation\Slideshow\Slides;

use JMS\Serializer\Annotation as JMS;

class SlideCollection
{
    /**
     * @var Slide[]|array
     *
     * @JMS\Type("array<AppBundle\Presentation\Slideshow\Slides\Slide>")
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
    public function setSlides(array $slides): void
    {
        $this->slides = $slides;
    }

    public function addSlide(Slide $slide): void
    {
        $this->slides[] = $slide;
    }
}
