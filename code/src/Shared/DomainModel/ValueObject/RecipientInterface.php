<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

interface RecipientInterface
{
    public function getEmail(): string;

    public function getPhone(): string;
}
