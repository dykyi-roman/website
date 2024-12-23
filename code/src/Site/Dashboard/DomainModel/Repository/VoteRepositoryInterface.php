<?php

declare(strict_types=1);

namespace Site\Dashboard\DomainModel\Repository;

use Site\Dashboard\DomainModel\ValueObject\Vote;

interface VoteRepositoryInterface
{
    public function getVotesByApp(string $appType): Vote;

    public function save(Vote $vote): void;
}
