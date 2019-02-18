<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity\Api;

use App\Entity\User;

class AccessKey
{
    /** @var null|int */
    protected $id;

    /** @var string */
    protected $code = '';

    /** @var string */
    protected $name = '';

    /** @var null|\DateTime */
    protected $last_use;

    /** @var string[]|array */
    protected $roles = [];

    /** @var null|User */
    protected $owner;

    /**
     * Generates a new code and sets it.
     */
    public function generateAndSetCode(): self
    {
        $code = sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
        $this->setCode($code);
        return $this;
    }

    public function getRolesReadable(): string
    {
        return implode(', ', $this->getRoles());
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
     * @param string[]|array $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string[]|array
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
