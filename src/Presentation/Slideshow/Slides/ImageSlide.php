<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow\Slides;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("NONE")
 */
class ImageSlide extends Slide
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
    protected $type = 'Image';

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
