<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow\Slides;

use JMS\Serializer\Annotation as JMS;

/**
 * @TODO Can Type be removed?
 */
#[JMS\ExclusionPolicy(JMS\ExclusionPolicy::NONE)]
class VideoSlide extends Slide
{
    #[JMS\Type('string')]
    protected string $src = '';

    #[JMS\Type('string')]
    protected string $type = 'Video';

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
