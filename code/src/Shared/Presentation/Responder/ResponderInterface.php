<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    public function respond(): Response;
}
