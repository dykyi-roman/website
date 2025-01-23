<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Model;

use Notifications\DomainModel\Model\TranslatableText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslatableText::class)]
final class TranslatableTextTest extends TestCase
{
    public function testCreateTranslatableText(): void
    {
        $messageId = 'test.message.id';
        $parameters = ['param1' => 'value1', 'param2' => 'value2'];

        $text = new TranslatableText($messageId, $parameters);

        $this->assertSame($messageId, $text->getMessageId());
        $this->assertSame($parameters, $text->getParameters());
    }

    public function testCreateTranslatableTextWithEmptyParameters(): void
    {
        $messageId = 'test.message.id';
        $text = new TranslatableText($messageId);

        $this->assertSame($messageId, $text->getMessageId());
        $this->assertSame([], $text->getParameters());
    }

    public function testCreateTranslatableTextWithNullParameters(): void
    {
        $messageId = 'test.message.id';
        $text = new TranslatableText($messageId, []);

        $this->assertSame($messageId, $text->getMessageId());
        $this->assertSame([], $text->getParameters());
    }
}
