<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Command;

use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\SymfonyJobQueue\Service\JobPruner;
use Dmstr\SymfonyJobQueue\State\JobPruneProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI mirror of POST /admin/jobs/prune, validated against the same schema.
 */
#[AsCommand(
    name: 'app:jobs:prune',
    description: 'Delete completed and failed jobs older than a number of days',
)]
final class PruneJobsCommand extends Command
{
    public function __construct(
        private readonly InputSchemaValidator $validator,
        private readonly JobPruner $pruner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Keep jobs younger than this many days', (string) JobPruner::DEFAULT_DAYS)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count the jobs that would be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = filter_var($input->getOption('days'), FILTER_VALIDATE_INT);
        if ($days === false) {
            $io->error('--days must be an integer');

            return Command::INVALID;
        }
        $body = ['days' => $days, 'dryRun' => (bool) $input->getOption('dry-run')];

        try {
            $this->validator->validate(JobPruneProcessor::OPERATION, $body);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }

        $result = $this->pruner->prune($body['days'], $body['dryRun']);
        $io->success(sprintf(
            '%s %d finished job(s) created before %s.',
            $result->dryRun ? 'Would delete' : 'Deleted',
            $result->deleted,
            $result->before->format(\DateTimeInterface::ATOM)
        ));

        return Command::SUCCESS;
    }
}
