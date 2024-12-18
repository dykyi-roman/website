<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Model;

use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Enum\PartnerStatus;
use App\Shared\DomainModel\Enum\Roles;
use App\Shared\DomainModel\ValueObject\Email;
use App\Shared\DomainModel\ValueObject\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'partner')]
#[ORM\HasLifecycleCallbacks]
class Partner implements UserInterface, PasswordAuthenticatedUserInterface, PartnerInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'partner_id', unique: true)]
    private PartnerId $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'email', length: 64)]
    private Email $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'text')]
    private ?string $avatar;

    #[ORM\Column(type: 'location')]
    private Location $location;

    #[ORM\Column(type: 'partner_status', options: ['default' => PartnerStatus::ACTIVE])]
    private PartnerStatus $status;

    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $token;

    #[ORM\Column(name: 'phone_verified_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $phoneVerifiedAt;

    #[ORM\Column(name: 'email_verified_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $emailVerifiedAt;

    #[ORM\Column(name: 'activated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $activatedAt;

    #[ORM\Column(name: 'deactivated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deactivatedAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        PartnerId $id,
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
        $this->status = PartnerStatus::ACTIVE;
        $this->roles = [Roles::ROLE_PARTNER->value];
        $this->activatedAt = new \DateTimeImmutable();
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

    public function updatePassword(string $password): void
    {
        $this->password = $password;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): PartnerId
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getStatus(): PartnerStatus
    {
        return $this->status;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPhoneVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->phoneVerifiedAt;
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function getActivatedAt(): ?\DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function getDeactivatedAt(): ?\DateTimeImmutable
    {
        return $this->deactivatedAt;
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
