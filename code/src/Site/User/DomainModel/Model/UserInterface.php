<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    public function getId(): UserId;

    public function getEmail(): Email;

    public function isActive(): bool;

    public function isDeleted(): bool;
}
