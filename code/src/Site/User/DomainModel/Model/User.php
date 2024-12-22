<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\User\DomainModel\Enum\Roles;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Enum\UserStatus;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\HasLifecycleCallbacks]
class User implements PasswordAuthenticatedUserInterface, UserInterface
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

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $token;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        string $name,
        Email $email,
        Location $location,
        ?string $phone = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->location = $location;
        $this->status = UserStatus::ACTIVATED;
        $this->roles = [Roles::ROLE_CLIENT->value];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function clearResetToken(): void
    {
        $this->token = null;
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

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function withReferral(string $referral): void
    {
        $this->referral = $referral;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function getToken(): ?string
    {
        return $this->token;
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

    /** @return array<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function getPassword(): string
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
}