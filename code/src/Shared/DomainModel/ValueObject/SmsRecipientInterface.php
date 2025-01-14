<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

interface SmsRecipientInterface extends RecipientInterface
{
    public function getPhone(): string;
}
