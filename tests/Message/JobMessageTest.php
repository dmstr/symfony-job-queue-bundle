<?php
// file generated with AI assistance: Claude Code - 2026-06-13 23:14:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Message;

use Dmstr\SymfonyJobQueue\Message\JobMessage;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the {@see JobMessage} Messenger envelope payload.
 */
final class JobMessageTest extends TestCase
{
    public function testCarriesJobId(): void
    {
        self::assertSame('job-123', (new JobMessage('job-123'))->getJobId());
    }

    public function testDistinctMessagesKeepTheirOwnId(): void
    {
        self::assertSame('a', (new JobMessage('a'))->getJobId());
        self::assertSame('b', (new JobMessage('b'))->getJobId());
    }
}
