<?php

namespace AppBundle\Entity\PresentationSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.11.17
 * @time     :  16:47
 */
class Slideshow
{
    /**
     * @var array
     * @JMS\Type("array<AppBundle\Entity\Slides\Slide>")
     */
    protected $slides;

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
     * @return Slideshow
     */
    public function setSlides($slides)
    {
        $this->slides = $slides;
        return $this;
    }
}
