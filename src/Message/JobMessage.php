<?php

// file generated with AI assistance: Claude Code - 2025-11-13 10:00:00

namespace Dmstr\SymfonyJobQueue\Message;

class JobMessage
{
    public function __construct(
        private readonly string $jobId
    ) {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
