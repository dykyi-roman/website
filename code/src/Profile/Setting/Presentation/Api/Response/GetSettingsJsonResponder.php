<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class GetSettingsJsonResponder implements ResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode;

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, array<string, mixed>> $settings
     */
    public function success(array $settings): self
    {
        $this->data = [
            'success' => true,
            'settings' => $settings,
        ];
        $this->statusCode = 200;

        return $this;
    }

    public function respond(): self
    {
        return $this;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
