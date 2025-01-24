<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Query;

use Shared\DomainModel\ValueObject\UserId;

/**
 * @see GetSettingsQueryHandler
 */
final readonly class GetSettingsQuery
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}
