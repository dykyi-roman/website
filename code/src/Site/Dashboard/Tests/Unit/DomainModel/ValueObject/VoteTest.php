<?php

declare(strict_types=1);

namespace Site\Dashboard\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Dashboard\DomainModel\ValueObject\Vote;

#[CoversClass(Vote::class)]
final class VoteTest extends TestCase
{
    public function testCreateVote(): void
    {
        $vote = Vote::create(5, 'android');

        $this->assertSame(5, $vote->getCount());
        $this->assertSame('android', $vote->getAppType());
    }

    public function testCreateZeroVote(): void
    {
        $vote = Vote::zero('ios');

        $this->assertSame(0, $vote->getCount());
        $this->assertSame('ios', $vote->getAppType());
    }

    public function testIncrementVote(): void
    {
        $vote = Vote::create(10, 'android');
        $incrementedVote = $vote->increment();

        $this->assertSame(11, $incrementedVote->getCount());
        $this->assertSame('android', $incrementedVote->getAppType());
        // Original vote should remain unchanged due to immutability
        $this->assertSame(10, $vote->getCount());
    }
}
