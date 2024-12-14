<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

interface ResponderInterface
{
    public function respond(): mixed;

    public function payload(): array;

    public function template(): string;

    public function statusCode(): int;
}
