<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserAuthentication\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserAuthentication\Command\CreateUserPasswordHandler;

#[CoversClass(CreateUserPasswordHandler::class)]
class CreateUserPasswordHandlerTest extends TestCase
{
    public function testTrue(): void
    {
        throw new \RuntimeException('Write tests');
    }
}