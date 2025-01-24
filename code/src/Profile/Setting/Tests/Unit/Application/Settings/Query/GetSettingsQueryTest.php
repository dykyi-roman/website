<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\Settings\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\Settings\Query\GetSettingsQuery;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(GetSettingsQuery::class)]
final class GetSettingsQueryTest extends TestCase
{
    public function testCreateQuery(): void
    {
        $userId = new UserId();
        $query = new GetSettingsQuery(userId: $userId);

        self::assertSame($userId, $query->userId);
    }

    public function testCreateQueryWithSpecificUserId(): void
    {
        $userId = UserId::fromString('c2a6aeb5-ae24-44ef-9d6e-6f58b6ad1f06');
        $query = new GetSettingsQuery(userId: $userId);

        self::assertSame($userId, $query->userId);
        self::assertSame('c2a6aeb5-ae24-44ef-9d6e-6f58b6ad1f06', $userId->toRfc4122());
    }
}
