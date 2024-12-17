<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Jwt;

use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use Firebase\JWT\JWT;

final readonly class JwtTokenGenerator implements TokenGeneratorInterface
{
    public function __construct(
        private string $passwordSecretKey,
    ) {
    }

    public function generate(string $value, int $ttl = 3600): string
    {
        $payload = [
            'sub' => $value,
            'iat' => time(),
            'exp' => time() + $ttl,
        ];

        return JWT::encode($payload, $this->passwordSecretKey, 'HS256');
    }
}