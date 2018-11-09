<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class ScheduledPresentation
{
    /** @var int */
    private $id;

    /** @var Screen */
    private $screen;

    /** @var Presentation */
    private $presentation;

    /** @var \DateTime */
    private $scheduled_start;

    /** @var \DateTime */
    private $scheduled_end;

    public function getId(): int
    {
        return $this->id;
    }

    public function setScreen(?Screen $screen = null): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getScreen(): ?Screen
    {
        return $this->screen;
    }

    public function setScheduledStart(\DateTime $scheduledStart): self
    {
        $this->scheduled_start = $scheduledStart;

        return $this;
    }

    public function getScheduledStart(): \DateTime
    {
        return $this->scheduled_start;
    }

    public function setScheduledEnd(\DateTime $scheduledEnd): self
    {
        $this->scheduled_end = $scheduledEnd;

        return $this;
    }

    public function getScheduledEnd(): \DateTime
    {
        return $this->scheduled_end;
    }

    public function setPresentation(?Presentation $presentation = null): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getPresentation(): ?Presentation
    {
        return $this->presentation;
    }
}
