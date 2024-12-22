<?php

declare(strict_types=1);

namespace Shared\Presentation\Responder;

interface TemplateResponderInterface extends ResponderInterface
{
    public function template(): string;

    /** @return array<string, string> */
    public function headers(): array;
}
