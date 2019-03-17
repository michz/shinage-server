<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

class RegistrationCode
{
    /** @var string|null */
    private $code;

    /** @var User|null */
    private $createdBy;

    /** @var \DateTimeInterface|null */
    private $createdDate;

    /** @var User|null */
    private $assignOrganization;

    /** @var \DateTimeInterface|null */
    private $validUntil;

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
