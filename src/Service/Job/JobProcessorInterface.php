<?php

// file generated with AI assistance: Claude Code - 2025-11-13 10:00:00

namespace Dmstr\SymfonyJobQueue\Service\Job;

use Dmstr\SymfonyJobQueue\Entity\Job;

interface JobProcessorInterface
{
    /**
     * Check if this processor supports the given job type
     */
    public function supports(string $jobType): bool;

    /**
     * Process the job and update its state
     *
     * @throws \Exception if processing fails
     */
    public function process(Job $job): void;
}
