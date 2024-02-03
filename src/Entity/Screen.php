<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class Screen
{
    protected string $guid;

    protected string $name = 'unbenannte Anzeige';

    protected string $location = '';

    protected string $notes = '';

    protected string $admin_c = '';

    protected \DateTime $first_connect;

    protected \DateTime $last_connect;

    protected string $connect_code = '';

    protected ?PresentationInterface $default_presentation = null;

    protected ?PresentationInterface $current_presentation = null;

    protected bool $alarming_enabled = false;

    protected string $alarming_mail_targets = '';

    protected int $alarming_connection_threshold = 30;

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

    public function isAlarmingEnabled(): bool
    {
        return $this->alarming_enabled;
    }

    public function setAlarmingEnabled(bool $alarming_enabled): self
    {
        $this->alarming_enabled = $alarming_enabled;
        return $this;
    }

    public function getAlarmingMailTargets(): string
    {
        return $this->alarming_mail_targets;
    }

    public function setAlarmingMailTargets(string $alarming_mail_targets): self
    {
        $this->alarming_mail_targets = $alarming_mail_targets;
        return $this;
    }

    public function getAlarmingConnectionThreshold(): int
    {
        return $this->alarming_connection_threshold;
    }

    public function setAlarmingConnectionThreshold(int $alarming_connection_threshold): self
    {
        $this->alarming_connection_threshold = $alarming_connection_threshold;
        return $this;
    }
}
