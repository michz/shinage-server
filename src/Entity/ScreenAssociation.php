<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'screen_associations')]
#[ORM\Entity]
class ScreenAssociation
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'screen_id', referencedColumnName: 'guid', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Screen::class)]
    protected Screen $screen;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $user;

    /** @var string[] */
    #[ORM\Column(name: 'roles', type: 'simple_array', length: 255, unique: false, nullable: false)]
    protected array $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param array|string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setScreen(Screen $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function setUser(?User $userId = null): self
    {
        $this->user = $userId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
