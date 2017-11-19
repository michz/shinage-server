<?php

namespace AppBundle\Entity\Slides;

use AppBundle\Entity\Slides\Slide;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.11.17
 * @time     :  20:11
 */
class SlideCollection
{
    /**
     * @var array<Slide>
     * @JMS\Type("array<AppBundle\Entity\Slides\Slide>")
     */
    protected $slides = [];

    /**
     * @return array
     */
    public function getSlides()
    {
        return $this->slides;
    }

    /**
     * @param array $slides
     *
     * @return SlideCollection
     */
    public function setSlides($slides)
    {
        $this->slides = $slides;
        return $this;
    }

    /**
     * @param Slide $slide
     *
     * @return $this
     */
    public function addSlide(Slide $slide)
    {
        $this->slides[] = $slide;
        return $this;
    }
}
