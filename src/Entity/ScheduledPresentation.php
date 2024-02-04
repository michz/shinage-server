<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class ScheduledPresentation
{
    private int $id;

    private Screen $screen;

    private PresentationInterface $presentation;

    private \DateTime $scheduled_start;

    private \DateTime $scheduled_end;

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

    public function setPresentation(?PresentationInterface $presentation = null): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getPresentation(): ?PresentationInterface
    {
        return $this->presentation;
    }
}
