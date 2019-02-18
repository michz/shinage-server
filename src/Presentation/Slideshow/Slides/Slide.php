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
class Slide
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $duration = 1000;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $title = 'Slide';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $transition = '';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type = '';

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }

    public function setTransition(string $transition): void
    {
        $this->transition = $transition;
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
