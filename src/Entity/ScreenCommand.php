<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class ScreenCommand
{
    protected int $id;

    protected Screen $screen;

    protected ?User $user;

    protected \DateTime $created;

    protected ?\DateTime $fetched;

    protected string $command = 'noop';

    /** @var string[] */
    protected array $arguments = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function setScreen(Screen $screen): void
    {
        $this->screen = $screen;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    public function getFetched(): ?\DateTime
    {
        return $this->fetched;
    }

    public function setFetched(?\DateTime $fetched): void
    {
        $this->fetched = $fetched;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string[] $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }
}
