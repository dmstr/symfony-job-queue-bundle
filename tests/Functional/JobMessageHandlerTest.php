<?php
// file generated with AI assistance: Claude Code - 2026-11-25 22:02:22 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Functional;

use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Message\JobMessage;
use Dmstr\SymfonyJobQueue\MessageHandler\JobMessageHandler;
use Dmstr\SymfonyJobQueue\Service\Job\JobProcessorInterface;
use Dmstr\SymfonyJobQueue\Service\Job\JobProcessorRegistry;
use Dmstr\SymfonyJobQueue\Tests\Functional\App\TestKernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class JobMessageHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * @return array<string, array{\Throwable}>
     */
    public static function throwables(): array
    {
        return [
            'php error' => [new \TypeError('boom from a type error')],
            'exception' => [new \RuntimeException('boom from a runtime exception')],
        ];
    }

    #[DataProvider('throwables')]
    public function testFailingProcessorMarksJobFailedAndRethrows(\Throwable $error): void
    {
        $job = (new Job())->setType('test');
        $this->em->persist($job);
        $this->em->flush();
        $jobId = $job->getId();

        // Throws before touching the database, so the EntityManager stays open
        $processor = new class($error) implements JobProcessorInterface {
            public function __construct(private readonly \Throwable $error)
            {
            }

            public function supports(string $jobType): bool
            {
                return true;
            }

            public function process(Job $job): void
            {
                throw $this->error;
            }
        };
        $handler = new JobMessageHandler($this->em, new JobProcessorRegistry([$processor]), new NullLogger());

        $caught = null;
        try {
            $handler(new JobMessage($jobId));
        } catch (\Throwable $caught) {
        }

        self::assertSame($error, $caught);

        $this->em->clear();
        $reloaded = $this->em->find(Job::class, $jobId);
        self::assertNotNull($reloaded);
        self::assertSame('failed', $reloaded->getStatus());
        self::assertSame($error->getMessage(), $reloaded->getErrorMessage());
        self::assertNotNull($reloaded->getCompletedAt());
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
