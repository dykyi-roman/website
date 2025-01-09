<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Shared\DomainModel\Model\AbstractDomainModel;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Profile\Setting\DomainModel\Event\UserActivatedEvent;
use Profile\Setting\DomainModel\Event\UserDeactivatedEvent;
use Profile\Setting\DomainModel\Event\UserDeletedEvent;
use Site\Registration\DomainModel\Event\UserChangedPasswordEvent;
use Site\User\DomainModel\Enum\Roles;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Enum\UserStatus;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User extends AbstractDomainModel implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_id', unique: true)]
    private UserId $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'email', length: 64)]
    private Email $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'location', nullable: true)]
    private Location $location;

    #[ORM\Column(type: 'user_status', options: ['default' => UserStatus::ACTIVATED])]
    private UserStatus $status;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $referral = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: 'password_token', type: 'string', length: 1024, nullable: true)]
    private ?string $passwordToken = null;

    #[ORM\Column(name: 'facebook_token', type: 'string', length: 1024, nullable: true)]
    private ?string $facebookToken = null;

    #[ORM\Column(name: 'google_token', type: 'string', length: 1024, nullable: true)]
    private ?string $googleToken = null;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string> $roles
     */
    public function __construct(
        UserId $id,
        string $name,
        Email $email,
        Location $location,
        ?string $phone = null,
        array $roles = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->location = $location;
        $this->status = UserStatus::ACTIVATED;
        $this->roles = [] === $roles ? [Roles::ROLE_CLIENT->value, Roles::ROLE_PARTNER->value] : $roles;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = UserStatus::ACTIVATED;

        $this->raise(
            new UserActivatedEvent(
                $this->id,
                $this->email,
                $this->name,
            ),
        );
    }

    public function deactivate(): void
    {
        $this->status = UserStatus::DEACTIVATED;

        $this->raise(
            new UserDeactivatedEvent(
                $this->id,
                $this->email,
                $this->name,
            ),
        );
    }

    public function delete(): void
    {
        $this->eraseCredentials();
        $this->deactivate();

        $this->raise(
            new UserDeletedEvent(
                $this->id,
                $this->email,
                $this->name,
            ),
        );

        $this->facebookToken = null;
        $this->googleToken = null;
        $this->password = null;
        $this->passwordToken = null;
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function clearResetToken(): void
    {
        $this->passwordToken = null;

        $this->raise(
            new UserChangedPasswordEvent(
                $this->id,
            ),
        );
    }

    public function getUserIdentifier(): string
    {
        return $this->email->value;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function updatePassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPasswordToken(string $passwordToken): void
    {
        $this->passwordToken = $passwordToken;
    }

    public function withReferral(string $referral): void
    {
        $this->referral = $referral;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function getPasswordToken(): ?string
    {
        return $this->passwordToken;
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function isVerified(): bool
    {
        return false;
    }

    /** @return array<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setDeletedAt(\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFacebookToken(): ?string
    {
        return $this->facebookToken;
    }

    public function getGoogleToken(): ?string
    {
        return $this->googleToken;
    }

    public function setGoogleToken(?string $googleToken): void
    {
        $this->googleToken = $googleToken;
    }

    public function setFacebookToken(?string $facebookToken): void
    {
        $this->facebookToken = $facebookToken;
    }
}
