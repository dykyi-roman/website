<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Service;

use Profile\User\Application\UserManagement\Exception\UserChangeDataException;
use Profile\User\Application\UserManagement\Exception\UserExistException;
use Profile\User\DomainModel\Exception\UserNotFoundException;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\UserId;

final readonly class UpdateUserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws UserExistException
     * @throws UserNotFoundException
     * @throws UserChangeDataException
     */
    public function update(
        UserId $userId,
        string $name,
        string $email,
        string $phone,
        ?string $avatar = null,
    ): void {
        $user = $this->userRepository->findById($userId);
        $newEmail = Email::fromString($email);

        if ($this->userRepository->findByEmail($newEmail) && !$user->email()->equals($newEmail)) {
            $this->logger->error('Attempted to update user with existing email');
            throw new UserExistException($user->id());
        }

        try {
            $user->changeName($name);
            $user->changeEmail($newEmail);
            $user->changePhone($phone);

            if (null !== $avatar) {
                $user->changeAvatar($avatar);
            }

            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw new UserChangeDataException($userId);
        }
    }
}
