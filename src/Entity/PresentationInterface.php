<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

interface PresentationInterface
{
    public function getId(): int;

    public function setTitle(string $title): self;

    public function getTitle(): string;

    public function setNotes(string $notes): self;

    public function getNotes(): string;

    public function setSettings(string $settings): self;

    public function getSettings(): string;

    public function getLastModified(): \DateTime;

    public function setLastModified(\DateTime $lastModified): void;

    public function setOwner(User $owner = null): self;

    public function getOwner(): User;

    public function getType(): string;

    public function setType(string $type): void;
}
