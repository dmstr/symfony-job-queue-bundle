<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Message;

use Dmstr\SymfonyJobQueue\Service\JobPruner;

/**
 * Asks for a prune run, e.g. as a recurring message of the application's
 * scheduler: `RecurringMessage::every('1 day', new PruneJobs())`.
 */
final class PruneJobs
{
    public function __construct(
        public readonly int $days = JobPruner::DEFAULT_DAYS,
    ) {
    }
}
