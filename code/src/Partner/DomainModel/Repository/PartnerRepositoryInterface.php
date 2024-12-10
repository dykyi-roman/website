<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Repository;

use App\Partner\DomainModel\Model\Partner;

interface PartnerRepositoryInterface
{
    public function save(Partner $partner): void;
    
    public function findById(string $id): ?Partner;
}
