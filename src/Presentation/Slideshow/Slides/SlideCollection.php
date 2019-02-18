<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow\Slides;

use JMS\Serializer\Annotation as JMS;

class SlideCollection
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
    public function setSlides(array $slides): void
    {
        $this->slides = $slides;
    }

    public function addSlide(Slide $slide): void
    {
        $this->slides[] = $slide;
    }
}
