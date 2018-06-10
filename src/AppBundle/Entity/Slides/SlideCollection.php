<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Entity\Slides;

use JMS\Serializer\Annotation as JMS;

class SlideCollection
{
    /**
     * @var Slide[]|array
     *
     * @JMS\Type("array<AppBundle\Entity\Slides\Slide>")
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
     *
     * @return SlideCollection
     */
    public function setSlides(array $slides): self
    {
        $this->slides = $slides;
        return $this;
    }

    /**
     * @return $this
     */
    public function addSlide(Slide $slide): self
    {
        $this->slides[] = $slide;
        return $this;
    }
}
