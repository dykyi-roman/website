<?php

declare(strict_types=1);

namespace Site\Dashboard\Tests\Unit\Application\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Site\Dashboard\Application\Service\VoteService;
use Site\Dashboard\DomainModel\Repository\VoteRepositoryInterface;
use Site\Dashboard\DomainModel\ValueObject\Vote;

#[CoversClass(VoteService::class)]
final class VoteServiceTest extends TestCase
{
    /** @var VoteRepositoryInterface&MockObject */
    private VoteRepositoryInterface $voteRepository;
    private VoteService $voteService;

    protected function setUp(): void
    {
        $this->voteRepository = $this->createMock(VoteRepositoryInterface::class);
        $this->voteService = new VoteService($this->voteRepository);
    }

    public function testGetVotes(): void
    {
        $expectedVote = Vote::create(5, 'android');

        $this->voteRepository
            ->expects($this->once())
            ->method('getVotesByApp')
            ->with('android')
            ->willReturn($expectedVote);

        $result = $this->voteService->getVotes('android');

        $this->assertSame($expectedVote, $result);
    }

    public function testIncrementVotes(): void
    {
        $initialVote = Vote::create(5, 'ios');
        $incrementedVote = $initialVote->increment();

        $this->voteRepository
            ->expects($this->once())
            ->method('getVotesByApp')
            ->with('ios')
            ->willReturn($initialVote);

        $this->voteRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Vote $vote) use ($incrementedVote) {
                return $vote->getCount() === $incrementedVote->getCount()
                    && $vote->getAppType() === $incrementedVote->getAppType();
            }));

        $this->voteService->incrementVotes('ios');
    }
}
