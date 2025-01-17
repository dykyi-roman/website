<?php

declare(strict_types=1);

namespace Profile\User\Application\UpdateUserSettings\Service;

use Profile\User\Application\UpdateUserSettings\Exception\UserExistException;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;

final readonly class UpdateUserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws UserExistException
     */
    public function update(
        UserId $userId,
        string $name,
        string $email,
        string $phone,
        ?string $avatar = null,
    ): void {
        try {
            $user = $this->userRepository->findById($userId);
            $newEmail = Email::fromString($email);

            if ($this->userRepository->findByEmail($newEmail) && !$user->email()->equals($newEmail)) {
                throw new UserExistException($user->id());
            }

            $user->changeName($name);
            $user->changeEmail($newEmail);
            $user->changePhone($phone);

            if (null !== $avatar) {
                $user->changeAvatar($avatar);
            }

            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
