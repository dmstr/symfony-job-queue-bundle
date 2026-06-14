<?php
// file generated with AI assistance: Claude Code - 2026-06-13 23:14:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Entity;

use Dmstr\SymfonyJobQueue\Entity\Job;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit tests for the {@see Job} entity exercised as a plain object — no
 * Doctrine, no database. Verifies the constructor defaults, fluent setters and
 * the PreUpdate lifecycle hook.
 */
final class JobTest extends TestCase
{
    public function testConstructorSetsSaneDefaults(): void
    {
        $job = new Job();

        self::assertTrue(Uuid::isValid($job->getId()), 'id must be a valid UUID');
        self::assertSame('pending', $job->getStatus());
        self::assertSame(0, $job->getProgress());
        self::assertSame([], $job->getInputData());
        self::assertNull($job->getResultData());
        self::assertNull($job->getErrorMessage());
        self::assertNull($job->getStartedAt());
        self::assertNull($job->getCompletedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $job->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $job->getUpdatedAt());
    }

    public function testEachJobGetsAUniqueId(): void
    {
        self::assertNotSame((new Job())->getId(), (new Job())->getId());
    }

    public function testFluentSettersUpdateState(): void
    {
        $job = new Job();
        $started = new \DateTimeImmutable('2026-01-01 10:00:00');
        $completed = new \DateTimeImmutable('2026-01-01 10:05:00');

        $result = $job
            ->setType('ref_project_scan')
            ->setStatus('completed')
            ->setInputData(['apiConfiguration' => ['uuid' => '5302d197']])
            ->setResultData(['imported' => 42])
            ->setErrorMessage('none')
            ->setProgress(100)
            ->setStartedAt($started)
            ->setCompletedAt($completed);

        self::assertSame($job, $result, 'setters must be fluent');
        self::assertSame('ref_project_scan', $job->getType());
        self::assertSame('completed', $job->getStatus());
        self::assertSame(['apiConfiguration' => ['uuid' => '5302d197']], $job->getInputData());
        self::assertSame(['imported' => 42], $job->getResultData());
        self::assertSame('none', $job->getErrorMessage());
        self::assertSame(100, $job->getProgress());
        self::assertSame($started, $job->getStartedAt());
        self::assertSame($completed, $job->getCompletedAt());
    }

    public function testOnPreUpdateRefreshesUpdatedAt(): void
    {
        $job = new Job();
        $job->onPreUpdate();

        self::assertInstanceOf(\DateTimeImmutable::class, $job->getUpdatedAt());
        self::assertGreaterThanOrEqual($job->getCreatedAt(), $job->getUpdatedAt());
    }
}
