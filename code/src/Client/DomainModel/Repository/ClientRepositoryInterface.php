<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Repository;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;

interface ClientRepositoryInterface
{
    public function save(Client $client): void;
    
    public function findById(ClientId $id): ?Client;

    public function findByEmail(string $email): ?Client;
}
