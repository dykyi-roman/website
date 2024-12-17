<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Repository;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Shared\DomainModel\ValueObject\Email;

interface ClientRepositoryInterface
{
    public function save(Client $client): void;

    public function findById(ClientId $id): ?Client;

    public function findByEmail(Email $email): ?Client;

    public function findByToken(string $token): ?Client;
}
