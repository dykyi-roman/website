<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Model;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Enum\ClientStatus;
use App\Shared\DomainModel\Enum\Roles;
use App\Shared\DomainModel\ValueObject\Email;
use App\Shared\DomainModel\ValueObject\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    #[ORM\Column(type: 'email', length: 64)]
    private Email $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'location', nullable: true)]
    private Location $location;

    #[ORM\Column(type: 'client_status', options: ['default' => ClientStatus::ACTIVE])]
    private ClientStatus $status;

    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 1024)]
    private string $token;

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
        Email $email,
        Location $location,
        ?string $phone = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->location = $location;
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
        return $this->email->value;
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

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getId(): ClientId
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
