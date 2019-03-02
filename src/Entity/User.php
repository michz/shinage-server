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

    /** @var string */
    protected $userType = UserType::USER_TYPE_USER;

    /** @var string */
    protected $name = '';

    /** @var Collection */
    private $organizations;

    /** @var Collection */
    private $users;

    /** @var string */
    protected $password;

    /** @var string */
    protected $plainPassword;

    /** @var bool */
    private $emailAuthEnabled = false;

    /** @var null|string */
    private $emailAuthCode;

    /** @var null|string[] */
    private $backupCodes;

    /** @var null|string */
    private $totpSecret;

    public function __construct()
    {
        parent::__construct();
        $this->organizations = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        parent::setUsername($email);
        parent::setUsernameCanonical($email);
        return parent::setEmail($email);
    }

    /**
     * @return string[]|array
     */
    public function getAllowedPoolPaths(): array
    {
        // @TODO Remove from here and move to own service
        $r = [];
        $r[] = 'user-' . $this->id;

        $orgas = $this->getOrganizations();
        foreach ($orgas as $orga) { /* @var User $orga */
            $r[] = 'user-' . $orga->getId();
        }

        return $r;
    }

    public function isPoolFileAllowed(string $path): bool
    {
        // @TODO Remove from here and move to own service
        $file = ltrim($path, "/\r\n\t ");
        $base = substr($file, 0, strpos($file, '/'));
        return \in_array($base, $this->getAllowedPoolPaths(), true);
    }

    public function isPresentationAllowed(PresentationInterface $presentation): bool
    {
        // @TODO Remove from here and move to own service

        if ($presentation->getOwner() === $this) {
            return true;
        }

        $orgas = $this->getOrganizations();
        foreach ($orgas as $orga) { /** @var User $orga */
            if ($presentation->getOwner() === $orga) {
                return true;
            }
        }

        return false;
    }

    public static function generateToken(): string
    {
        // @TODO Remove from here and move to own service
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
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

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): self
    {
        $this->users = $users;
        return $this;
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
}
