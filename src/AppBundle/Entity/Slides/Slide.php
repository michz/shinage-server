<?php

namespace AppBundle\Entity\Slides;

use JMS\Serializer\Annotation as JMS;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.11.17
 * @time     :  19:33
 *
 * @JMS\ExclusionPolicy("NONE")
 */
class Slide
{
    /**
     * @var int
     * @JMS\Type("integer")
     */
    protected $duration = 1000;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $title = 'Slide';

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $transition = '';

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $type = '';

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return Slide
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Slide
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param string $transition
     *
     * @return Slide
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;
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
     * @return Slide
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
