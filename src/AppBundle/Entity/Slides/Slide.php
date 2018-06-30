<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Entity\Slides;

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

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }

    public function setTransition(string $transition): self
    {
        $this->transition = $transition;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
