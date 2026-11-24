<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\MessageHandler;

use Dmstr\SymfonyJobQueue\Message\PruneJobs;
use Dmstr\SymfonyJobQueue\Service\JobPruner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PruneJobsHandler
{
    public function __construct(
        private readonly JobPruner $pruner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PruneJobs $message): void
    {
        $result = $this->pruner->prune($message->days);
        $this->logger->info('Pruned finished jobs', [
            'deleted' => $result->deleted,
            'before' => $result->before->format(\DateTimeInterface::ATOM),
        ]);
    }
}
