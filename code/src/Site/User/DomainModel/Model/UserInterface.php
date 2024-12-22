<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    public function isActive(): bool;

    public function isDeleted(): bool;
}
