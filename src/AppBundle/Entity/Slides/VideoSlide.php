<?php

namespace AppBundle\Entity\Slides;

use JMS\Serializer\Annotation as JMS;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.02.2018
 * @time     :  20:52
 *
 * @JMS\ExclusionPolicy("NONE")
 */
class VideoSlide extends Slide
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $src = '';

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $type = 'Video';

    /**
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param string $src
     *
     * @return ImageSlide
     */
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return ImageSlide
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
