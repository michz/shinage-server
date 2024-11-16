<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'screen_commands')]
#[ORM\Index(columns: ['screen_id', 'fetched'], name: 'screen_fetched_index')]
#[ORM\Entity]
class ScreenCommand
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'screen_id', referencedColumnName: 'guid', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Screen::class)]
    protected Screen $screen;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $user;

    #[ORM\Column(name: 'created', type: 'datetime', unique: false, nullable: false)]
    protected \DateTime $created;

    #[ORM\Column(name: 'fetched', type: 'datetime', unique: false, nullable: true)]
    protected ?\DateTime $fetched;

    #[ORM\Column(name: 'command', type: 'string', length: 255, unique: false, nullable: false)]
    protected string $command = 'noop';

    /** @var string[] */
    #[ORM\Column(name: 'arguments', type: 'json', unique: false, nullable: false)]
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
