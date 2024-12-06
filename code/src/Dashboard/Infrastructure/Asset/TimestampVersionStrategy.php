<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final readonly class TimestampVersionStrategy implements VersionStrategyInterface
{
    public function getVersion(string $path): string
    {
        return (string) time();
    }

    public function applyVersion(string $path): string
    {
        return sprintf('%s?v=%s', $path, $this->getVersion($path));
    }
}
