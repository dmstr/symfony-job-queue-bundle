<?php

// file generated with AI assistance: Claude Code - 2025-11-13 10:00:00

namespace Dmstr\SymfonyJobQueue\Service\Job;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class JobProcessorRegistry
{
    /**
     * @param iterable<JobProcessorInterface> $processors
     */
    public function __construct(
        #[TaggedIterator('app.job_processor')]
        private readonly iterable $processors
    ) {
    }

    /**
     * Get the appropriate processor for the given job type
     *
     * @throws \RuntimeException if no processor found
     */
    public function getProcessor(string $jobType): JobProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($jobType)) {
                return $processor;
            }
        }

        throw new \RuntimeException(sprintf('No processor found for job type "%s"', $jobType));
    }
}
