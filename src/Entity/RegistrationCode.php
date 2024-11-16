<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'registration_codes')]
#[ORM\Entity]
class RegistrationCode
{
    #[ORM\Column(name: 'code', type: 'string', length: 250)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private ?string $code = null;

    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?User $createdBy = null;

    #[ORM\Column(name: 'created_date', type: 'datetime', unique: false, nullable: false)]
    private ?\DateTimeInterface $createdDate = null;

    #[ORM\JoinColumn(name: 'assign_organization', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?User $assignOrganization = null;

    #[ORM\Column(name: 'valid_until', type: 'datetime', unique: false, nullable: false)]
    private ?\DateTimeInterface $validUntil = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    public function getAssignOrganization(): ?User
    {
        return $this->assignOrganization;
    }

    public function setAssignOrganization(?User $assignOrganization): void
    {
        $this->assignOrganization = $assignOrganization;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeInterface $validUntil): void
    {
        $this->validUntil = $validUntil;
    }
}
