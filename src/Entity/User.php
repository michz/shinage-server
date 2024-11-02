<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Entity;

use App\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as TwoFactorGoogleInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'users')]
#[ORM\Entity]
class User implements UserInterface, TwoFactorEmailInterface, TwoFactorGoogleInterface, BackupCodeInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'user_type', type: 'string', length: 32)]
    private string $userType = UserType::USER_TYPE_USER;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name = '';

    #[ORM\Column(name: 'username', type: 'string', length: 180)]
    private string $username = '';

    #[ORM\Column(name: 'username_canonical', type: 'string', length: 180, unique: true)]
    private string $usernameCanonical = '';

    #[ORM\Column(name: 'email', type: 'string', length: 180)]
    private string $email = '';

    #[ORM\Column(name: 'email_canonical', type: 'string', length: 180, unique: true)]
    private string $emailCanonical = '';

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    private bool $enabled = true;

    /** @var Collection<array-key, User> */
    #[ORM\JoinTable(name: 'users_orgas')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\InverseJoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'users')]
    private Collection $organizations;

    /** @var Collection<array-key, User> */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'organizations')]
    private Collection $users;

    #[ORM\Column(name: 'password', type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    protected ?\DateTime $lastLogin = null;

    #[ORM\Column(name: 'confirmation_token', type: 'string', length: 180, unique: true, nullable: true)]
    protected ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $passwordRequestedAt = null;

    /**
     * @var string[]
     */
    #[ORM\Column(name: 'roles', type: 'json')]
    protected array $roles = [];

    #[ORM\Column(name: 'email_auth_enabled', type: 'boolean', unique: false, nullable: false)]
    private bool $emailAuthEnabled = false;

    #[ORM\Column(name: 'email_auth_code', type: 'string', length: 6, unique: false, nullable: true)]
    private ?string $emailAuthCode = null;

    /** @var string[]|null */
    #[ORM\Column(name: 'backup_codes', type: 'json', unique: false, nullable: true)]
    private ?array $backupCodes = null;

    #[ORM\Column(name: 'totp_secret', type: 'string', length: 200, unique: false, nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(name: 'auto_assign_by_mailhost', type: 'boolean', unique: false, nullable: false)]
    private bool $orgaAssignAutomaticallyByMailHost = false;

    public function __construct()
    {
        $this->organizations = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->emailCanonical = $email;
        $this->username = $email;
        $this->usernameCanonical = $email;

        return $this;
    }

    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
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

    /**
     * {@inheritDoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function addRole(string $role): self
    {
        $role = \strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
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

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->emailCanonical;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin = null): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt = null): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }
}
