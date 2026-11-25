<?php
// file generated with AI assistance: Claude Code - 2026-11-25 23:10:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional;

use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Message\JobMessage;
use Dmstr\SymfonyJobQueue\Service\JobQueueService;
use Dmstr\SymfonyJobQueue\State\JobPruneProcessor;
use Dmstr\SymfonyJobQueue\Tests\Functional\App\TestKernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class JobQueueServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    /** @var list<object> */
    private array $dispatched = [];

    private JobQueueService $service;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        // The test bus handles messages synchronously and no processor exists,
        // so a recording bus replaces it before the service is built; the
        // validator stays the container's one with the full resolver chain.
        $bus = new class($this->dispatched) implements MessageBusInterface {
            /** @param list<object> $dispatched */
            public function __construct(private array &$dispatched)
            {
            }

            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $this->dispatched[] = $message;

                return new Envelope($message, $stamps);
            }
        };
        static::getContainer()->set('messenger.bus.default', $bus);
        $this->service = static::getContainer()->get(JobQueueService::class);
    }

    public function testInvalidInputIsRejectedWith422(): void
    {
        $caught = null;
        try {
            $this->service->createAndDispatch('test', ['foo' => 1], JobPruneProcessor::OPERATION);
        } catch (UnprocessableEntityHttpException $caught) {
        }

        self::assertNotNull($caught, 'Invalid input must be rejected');
        self::assertSame(422, $caught->getStatusCode());
        self::assertStringStartsWith('Input validation failed for operation "job_prune"', $caught->getMessage());
        self::assertSame(0, $this->jobCount());
        self::assertSame([], $this->dispatched);
    }

    public function testOperationWithoutSchemaIsALogicError(): void
    {
        $caught = null;
        try {
            $this->service->createAndDispatch('test', ['foo' => 1], 'unknown_operation');
        } catch (\LogicException $caught) {
        }

        self::assertNotNull($caught, 'An operation without input schema must not be skipped');
        self::assertStringContainsString('No input schema for operation', $caught->getMessage());
        self::assertSame(0, $this->jobCount());
        self::assertSame([], $this->dispatched);
    }

    public function testWithoutOperationNameTheInputIsNotValidated(): void
    {
        $job = $this->service->createAndDispatch('test', ['anything' => ['goes' => true]]);

        self::assertSame('pending', $job->getStatus());
        self::assertSame(1, $this->jobCount());
        $this->assertDispatchedFor($job);
    }

    public function testEmptyInputIsValidatedAsObject(): void
    {
        $job = $this->service->createAndDispatch('test', [], JobPruneProcessor::OPERATION);

        self::assertSame('pending', $job->getStatus());
        self::assertSame(1, $this->jobCount());
        $this->assertDispatchedFor($job);
    }

    public function testValidInputCreatesJob(): void
    {
        $input = ['days' => 10, 'dryRun' => true];
        $job = $this->service->createAndDispatch('test', $input, JobPruneProcessor::OPERATION);

        self::assertSame('pending', $job->getStatus());
        $this->assertDispatchedFor($job);

        $this->em->clear();
        $reloaded = $this->em->find(Job::class, $job->getId());
        self::assertNotNull($reloaded);
        self::assertSame($input, $reloaded->getInputData());
    }

    private function assertDispatchedFor(Job $job): void
    {
        self::assertCount(1, $this->dispatched);
        self::assertInstanceOf(JobMessage::class, $this->dispatched[0]);
        self::assertSame($job->getId(), $this->dispatched[0]->getJobId());
    }

    private function jobCount(): int
    {
        return (int) $this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM dmstr_job');
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
