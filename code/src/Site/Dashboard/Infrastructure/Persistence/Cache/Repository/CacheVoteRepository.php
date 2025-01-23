<?php

declare(strict_types=1);

namespace Site\Dashboard\Infrastructure\Persistence\Cache\Repository;

use Psr\SimpleCache\CacheInterface;
use Site\Dashboard\DomainModel\Repository\VoteRepositoryInterface;
use Site\Dashboard\DomainModel\ValueObject\Vote;

final readonly class CacheVoteRepository implements VoteRepositoryInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getVotesByApp(string $appType): Vote
    {
        $value = $this->cache->get("app_votes_{$appType}");

        if (is_int($value)) {
            return Vote::create($value, $appType);
        }

        if (is_string($value) && is_numeric($value)) {
            return Vote::create((int) $value, $appType);
        }

        return Vote::zero($appType);
    }

    public function save(Vote $vote): void
    {
        $this->cache->set("app_votes_{$vote->getAppType()}", $vote->getCount());
    }
}
