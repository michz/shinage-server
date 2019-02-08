<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class Screen
{
    /** @var string */
    protected $guid;

    /** @var string */
    protected $name = 'unbenannte Anzeige';

    /** @var string */
    protected $location = '';

    /** @var string */
    protected $notes = '';

    /** @var string */
    protected $admin_c = '';

    /** @var \DateTime */
    protected $first_connect;

    /** @var \DateTime */
    protected $last_connect;

    /** @var string */
    protected $connect_code = '';

    /** @var PresentationInterface */
    protected $default_presentation;

    /** @var PresentationInterface */
    protected $current_presentation = null;

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setAdminC(string $adminC): self
    {
        $this->admin_c = $adminC;

        return $this;
    }

    public function getAdminC(): string
    {
        return $this->admin_c;
    }

    public function setFirstConnect(\DateTime $firstConnect): self
    {
        $this->first_connect = $firstConnect;

        return $this;
    }

    public function getFirstConnect(): \DateTime
    {
        return $this->first_connect;
    }

    public function setLastConnect(\DateTime $lastConnect): self
    {
        $this->last_connect = $lastConnect;

        return $this;
    }

    public function getLastConnect(): \DateTime
    {
        return $this->last_connect;
    }

    public function setConnectCode(string $connectCode): self
    {
        $this->connect_code = $connectCode;

        return $this;
    }

    public function getConnectCode(): string
    {
        return $this->connect_code;
    }

    public function setDefaultPresentation(?PresentationInterface $presentation): self
    {
        $this->default_presentation = $presentation;

        return $this;
    }

    public function getDefaultPresentation(): ?PresentationInterface
    {
        return $this->default_presentation;
    }

    public function setCurrentPresentation(?PresentationInterface $presentation = null): self
    {
        $this->current_presentation = $presentation;
        return $this;
    }

    public function getCurrentPresentation(): ?PresentationInterface
    {
        return $this->current_presentation;
    }
}
