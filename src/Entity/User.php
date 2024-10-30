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
use FOS\UserBundle\Model\User as BaseUser;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as TwoFactorGoogleInterface;

#[ORM\Table(name: 'users')]
#[ORM\Entity]
class User extends BaseUser implements TwoFactorEmailInterface, TwoFactorGoogleInterface, BackupCodeInterface
{
    /** @var int */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    #[ORM\Column(name: 'user_type', type: 'string', length: 32, unique: false, nullable: false)]
    protected string $userType = UserType::USER_TYPE_USER;

    #[ORM\Column(name: 'name', type: 'string', length: 200, unique: false, nullable: false)]
    protected string $name = '';

    /** @var Collection<array-key, User> */
    #[ORM\JoinTable(name: 'users_orgas')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\InverseJoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'users')]
    private Collection $organizations;

    /** @var Collection<array-key, User> */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'organizations')]
    private Collection $users;

    /** @var string */
    protected $password;

    /** @var string */
    protected $plainPassword;

    #[ORM\Column(name: 'email_auth_enabled', type: 'boolean', unique: false, nullable: false)]
    private bool $emailAuthEnabled = false;

    #[ORM\Column(name: 'email_auth_code', type: 'string', length: 6, unique: false, nullable: true)]
    private ?string $emailAuthCode;

    /** @var string[]|null */
    #[ORM\Column(name: 'backup_codes', type: 'json', unique: false, nullable: true)]
    private ?array $backupCodes;

    #[ORM\Column(name: 'totp_secret', type: 'string', length: 200, unique: false, nullable: true)]
    private ?string $totpSecret;

    #[ORM\Column(name: 'auto_assign_by_mailhost', type: 'boolean', unique: false, nullable: false)]
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
