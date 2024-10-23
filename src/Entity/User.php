<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use App\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as TwoFactorGoogleInterface;

class User extends BaseUser implements TwoFactorEmailInterface, TwoFactorGoogleInterface, BackupCodeInterface
{
    /** @var int */
    protected $id;

    protected string $userType = UserType::USER_TYPE_USER;

    protected string $name = '';

    /** @var Collection<array-key, User> */
    private Collection $organizations;

    /** @var Collection<array-key, User> */
    private Collection $users;

    /** @var string */
    protected $password;

    /** @var string */
    protected $plainPassword;

    private bool $emailAuthEnabled = false;

    private ?string $emailAuthCode;

    /** @var string[]|null */
    private ?array $backupCodes;

    private ?string $totpSecret;

    private bool $orgaAssignAutomaticallyByMailHost = false;

    public function __construct()
    {
        parent::__construct();
        $this->organizations = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email): BaseUser|User|\FOS\UserBundle\Model\UserInterface
    {
        parent::setUsername($email);
        parent::setUsernameCanonical($email);
        return parent::setEmail($email);
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function addOrganization(self $organization): self
    {
        $this->organizations[] = $organization;

        return $this;
    }

    public function removeOrganization(self $organization): void
    {
        $this->organizations->removeElement($organization);
    }

    /** @return Collection<array-key, User> */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
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

    /** @return Collection<array-key, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): self
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(self $user): void
    {
        $this->users->removeElement($user);
    }

    public function isEmailAuthEnabled(): bool
    {
        return $this->emailAuthEnabled;
    }

    public function setEmailAuthEnabled(bool $enable): void
    {
        $this->emailAuthEnabled = $enable;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }

    public function getEmailAuthCode(): string
    {
        return $this->emailAuthCode ?: '';
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;
    }

    /**
     * @return string[]
     */
    public function getBackupCodes(): array
    {
        return $this->backupCodes ?: [];
    }

    /**
     * @param string[] $codes
     */
    public function setBackupCodes(array $codes): void
    {
        $this->backupCodes = $codes;
    }

    public function isBackupCode(string $code): bool
    {
        return null !== $this->backupCodes && \in_array($code, $this->backupCodes);
    }

    public function invalidateBackupCode(string $code): void
    {
        if (null === $this->backupCodes) {
            return;
        }

        $key = \array_search($code, $this->backupCodes);
        if (false !== $key) {
            unset($this->backupCodes[$key]);
        }
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->totpSecret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getEmail();
    }

    public function getGoogleAuthenticatorSecret(): string
    {
        return $this->totpSecret ?: '';
    }

    public function setGoogleAuthenticatorSecret(?string $secret): void
    {
        $this->totpSecret = $secret;
    }

    public function isOrgaAssignAutomaticallyByMailHost(): bool
    {
        return $this->orgaAssignAutomaticallyByMailHost;
    }

    public function setOrgaAssignAutomaticallyByMailHost(bool $orgaAssignAutomaticallyByMailHost): void
    {
        $this->orgaAssignAutomaticallyByMailHost = $orgaAssignAutomaticallyByMailHost;
    }
}
