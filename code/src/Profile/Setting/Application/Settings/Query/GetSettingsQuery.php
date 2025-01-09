<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Query;

use Site\User\DomainModel\Enum\UserId;

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
