<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow\Slides;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("NONE")
 *
 * @TODO Can Type be removed?
 */
class VideoSlide extends Slide
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $src = '';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type = 'Video';

    public function getSrc(): string
    {
        return $this->src;
    }

    public function setSrc(string $src): void
    {
        $this->src = $src;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
