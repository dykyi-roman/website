<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

interface ReferralReceiverInterface
{
    public function referral(string $name = 'reff'): string;
}
