<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use JMS\Serializer\Annotation as JMS;

class Presentation implements PresentationInterface
{
    /**
     * @JMS\Type("integer")
     */
    protected int $id;

    /**
     * @JMS\Type("string")
     */
    protected string $title = 'Presentation';

    /**
     * @JMS\Type("string")
     */
    protected string $notes = '';

    /**
     * @JMS\Exclude()
     */
    protected string $settings = '';

    /**
     * @JMS\Type("DateTime")
     */
    protected \DateTime $lastModified;

    protected User $owner;

    /**
     * @JMS\Type("string")
     */
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
