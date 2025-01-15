<?php

declare(strict_types=1);

namespace Site\Dashboard\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Site\Dashboard\DomainModel\ValueObject\Vote;
use Site\Dashboard\Infrastructure\Repository\CacheVoteRepository;

#[CoversClass(CacheVoteRepository::class)]
final class CacheVoteRepositoryTest extends TestCase
{
    private CacheInterface&MockObject $cache;
    private CacheVoteRepository $repository;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->repository = new CacheVoteRepository($this->cache);
    }

    public function testGetVotesByAppReturnsVoteWhenIntegerValueExists(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->with('app_votes_android')
            ->willReturn(5);

        $vote = $this->repository->getVotesByApp('android');

        $this->assertSame(5, $vote->getCount());
        $this->assertSame('android', $vote->getAppType());
    }

    public function testGetVotesByAppReturnsVoteWhenStringNumericValueExists(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->with('app_votes_ios')
            ->willReturn('10');

        $vote = $this->repository->getVotesByApp('ios');

        $this->assertSame(10, $vote->getCount());
        $this->assertSame('ios', $vote->getAppType());
    }

    public function testGetVotesByAppReturnsZeroVoteWhenNoValueExists(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->with('app_votes_android')
            ->willReturn(null);

        $vote = $this->repository->getVotesByApp('android');

        $this->assertSame(0, $vote->getCount());
        $this->assertSame('android', $vote->getAppType());
    }

    public function testSaveVotePersistsToCache(): void
    {
        $vote = Vote::create(15, 'ios');

        $this->cache->expects($this->once())
            ->method('set')
            ->with('app_votes_ios', 15);

        $this->repository->save($vote);
    }
}
