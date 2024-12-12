<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use App\Registration\DomainModel\ValueObject\Email;

final readonly class RegistrationService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository
    ) {
    }

    public function isEmailUnique(Email $email): bool
    {
        $clientExists = $this->clientRepository->findByEmail($email->value());
        $partnerExists = $this->partnerRepository->findByEmail($email->value());

        return !($clientExists || $partnerExists);
    }
}
