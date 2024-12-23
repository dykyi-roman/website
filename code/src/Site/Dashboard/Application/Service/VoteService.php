<?php

declare(strict_types=1);

namespace Site\Dashboard\Application\Service;

use Site\Dashboard\DomainModel\Repository\VoteRepositoryInterface;
use Site\Dashboard\DomainModel\ValueObject\Vote;

final readonly class VoteService
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
    ) {
    }

    public function getVotes(string $appType): Vote
    {
        return $this->voteRepository->getVotesByApp($appType);
    }

    public function incrementVotes(string $appType): void
    {
        $vote = $this->voteRepository->getVotesByApp($appType);
        $this->voteRepository->save($vote->increment());
    }
}
