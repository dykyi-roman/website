<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Model;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Enum\ClientStatus;
use App\Shared\Domain\Enum\Roles;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'client')]
#[ORM\HasLifecycleCallbacks]
class Client implements UserInterface, PasswordAuthenticatedUserInterface, ClientInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'client_id', unique: true)]
    private ClientId $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private ?string $avatar;

    #[ORM\Column(type: 'string', length: 64)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $city;

    #[ORM\Column(type: 'client_status', options: ['default' => ClientStatus::ACTIVE])]
    private ClientStatus $status;

    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

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
        ClientId $id,
        string $name,
        string $email,
        ?string $phone = null,
        ?string $country = null,
        ?string $city = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->status = ClientStatus::ACTIVE;
        $this->roles = [Roles::ROLE_CLIENT->value];
        $this->activatedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getId(): ClientId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getStatus(): ClientStatus
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

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
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