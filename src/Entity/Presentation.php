<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'presentations')]
#[ORM\Entity]
class Presentation implements PresentationInterface
{
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[JMS\Type('string')]
    #[ORM\Column(name: 'title', type: 'string', length: 200, unique: false, nullable: false)]
    protected string $title = 'Presentation';

    #[JMS\Type('string')]
    #[ORM\Column(name: 'notes', type: 'text', unique: false, nullable: false)]
    protected string $notes = '';

    #[JMS\Exclude()]
    #[ORM\Column(name: 'settings', type: 'text', unique: false, nullable: false)]
    protected string $settings = '';

    #[JMS\Type('DateTime')]
    #[ORM\Column(name: 'last_modified', type: 'datetime', unique: false, nullable: true)]
    protected \DateTime $lastModified;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected User $owner;

    #[JMS\Type('string')]
    #[ORM\Column(name: 'type', type: 'string', length: 200, unique: false, nullable: false)]
    protected string $type;

    public function __construct()
    {
        $this->lastModified = new \DateTime();
    }

    public function setId(int $id): PresentationInterface
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $title): PresentationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setNotes(string $notes): PresentationInterface
    {
        $this->notes = $notes;

        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setSettings(string $settings): PresentationInterface
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSettings(): string
    {
        return $this->settings;
    }

    public function getLastModified(): \DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(\DateTime $lastModified): void
    {
        $this->lastModified = $lastModified;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function setOwner(User $owner = null): PresentationInterface
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
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
