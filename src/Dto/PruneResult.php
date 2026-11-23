<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Outcome of a prune run: the records created before `$before` that were
 * deleted, or would be deleted in a dry run.
 */
final class PruneResult
{
    /** Serialization group of the prune operation's response */
    public const string GROUP = 'job:prune_result';

    public function __construct(
        #[Groups([self::GROUP])]
        public readonly int $days,
        #[Groups([self::GROUP])]
        public readonly \DateTimeImmutable $before,
        #[Groups([self::GROUP])]
        public readonly int $deleted,
        #[Groups([self::GROUP])]
        public readonly bool $dryRun,
    ) {
    }
}
