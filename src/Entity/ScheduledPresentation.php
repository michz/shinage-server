<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'schedule')]
#[ORM\Entity]
class ScheduledPresentation
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\JoinColumn(name: 'screen_id', referencedColumnName: 'guid', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Screen::class, fetch: 'EAGER')]
    private Screen $screen;

    #[ORM\JoinColumn(name: 'presentation_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Presentation::class, fetch: 'EAGER')]
    private PresentationInterface $presentation;

    #[ORM\Column(name: 'scheduled_start', type: 'datetime', unique: false, nullable: false)]
    private \DateTime $scheduled_start;

    #[ORM\Column(name: 'scheduled_end', type: 'datetime', unique: false, nullable: false)]
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
