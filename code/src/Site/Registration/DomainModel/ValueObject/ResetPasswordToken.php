<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\ValueObject;

use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

final readonly class ResetPasswordToken
{
    private string $token;

    public function __construct(TokenGeneratorInterface $tokenGenerator)
    {
        $this->token = $tokenGenerator->generateToken();
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
