<?php

// file generated with AI assistance: Claude Code - 2025-11-13 10:00:00

namespace Dmstr\SymfonyJobQueue\MessageHandler;

use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\Message\JobMessage;
use Dmstr\SymfonyJobQueue\Service\Job\JobProcessorRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class JobMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JobProcessorRegistry $processorRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(JobMessage $message): void
    {
        $jobId = $message->getJobId();
        $this->logger->info('Processing job', ['job_id' => $jobId]);

        // Load job entity
        $job = $this->entityManager->getRepository(Job::class)->find($jobId);
        if (!$job) {
            $this->logger->error('Job not found', ['job_id' => $jobId]);
            throw new \RuntimeException(sprintf('Job with ID "%s" not found', $jobId));
        }

        // Update status to running
        $job->setStatus('running');
        $job->setStartedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        try {
            // Get processor and execute
            $processor = $this->processorRegistry->getProcessor($job->getType());
            $processor->process($job);

            // Update status to completed
            $job->setStatus('completed');
            $job->setCompletedAt(new \DateTimeImmutable());
            $job->setProgress(100);

            $this->logger->info('Job completed successfully', [
                'job_id' => $jobId,
                'type' => $job->getType()
            ]);
        } catch (\Exception $e) {
            // Update status to failed
            $job->setStatus('failed');
            $job->setCompletedAt(new \DateTimeImmutable());
            $job->setErrorMessage($e->getMessage());

            $this->logger->error('Job failed', [
                'job_id' => $jobId,
                'type' => $job->getType(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger messenger retry mechanism
            throw $e;
        } finally {
            $this->entityManager->flush();
        }
    }
}
