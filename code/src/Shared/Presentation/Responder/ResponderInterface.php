<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

interface ResponderInterface
{
    public function respond(): self;

    public function payload(): array;

    public function statusCode(): int;
}
