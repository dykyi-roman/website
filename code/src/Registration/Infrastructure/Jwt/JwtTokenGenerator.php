<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Jwt;

use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public function isValid(string $token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->passwordSecretKey, 'HS256'));

            // Token has expired
            return $decoded->exp >= time();
        } catch (ExpiredException $exception) {
            // Token has expired
            return false;
        } catch (\Throwable) {
            // Invalid token structure or signature
            return false;
        }
    }
}