<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\Api;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'api_access_keys')]
#[ORM\Entity]
class AccessKey
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id;

    #[ORM\Column(name: 'code', type: 'string', length: 32, unique: true, nullable: false)]
    protected string $code = '';

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: false, nullable: false)]
    protected string $name = '';

    #[ORM\Column(name: 'last_use', type: 'datetime', unique: false, nullable: true)]
    protected ?\DateTime $last_use;

    /** @var string[]|array */
    #[ORM\Column(name: 'roles', type: 'simple_array', unique: false, nullable: false)]
    protected array $roles = [];

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $owner;

    /**
     * Generates a new code and sets it.
     */
    public function generateAndSetCode(): self
    {
        $code = \sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff),
            \random_int(0, 0xffff)
        );
        $this->setCode($code);
        return $this;
    }

    public function getRolesReadable(): string
    {
        return \implode(', ', $this->getRoles());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
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

    public function setLastUse(?\DateTime $lastUse): self
    {
        $this->last_use = $lastUse;

        return $this;
    }

    public function getLastUse(): ?\DateTime
    {
        return $this->last_use;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setOwner(?User $owner = null): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }
}
