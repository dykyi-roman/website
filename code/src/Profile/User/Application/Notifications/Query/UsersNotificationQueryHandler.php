<?php

declare(strict_types=1);

namespace Profile\User\Application\Notifications\Query;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UsersNotificationQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @return UserId[]
     */
    public function __invoke(UsersNotificationQuery $query): array
    {
        return $this->userRepository->findAll();
    }
}
