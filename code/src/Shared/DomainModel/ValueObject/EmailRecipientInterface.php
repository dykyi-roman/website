<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

interface EmailRecipientInterface extends RecipientInterface
{
    public function getEmail(): string;
}
