<?php
// file generated with AI assistance: Claude Code - 2026-11-23 22:50:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Service;

use Dmstr\SymfonyJobQueue\Dto\PruneResult;
use Dmstr\SymfonyJobQueue\Entity\Job;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Deletes finished jobs (`completed`, `failed`) created more than a number
 * of days ago; `pending` and `running` jobs are kept regardless of age.
 *
 * Works on DBAL in batches of ids, so a large table is not loaded into the
 * entity manager and no single statement locks it for long. The age is taken
 * from `created_at`, the indexed column. MySQL does not return the freed
 * space to the file system on its own; see the README.
 */
class JobPruner
{
    public const int DEFAULT_DAYS = 30;
    public const array FINISHED_STATUSES = ['completed', 'failed'];
    private const int BATCH_SIZE = 1000;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function prune(int $days = self::DEFAULT_DAYS, bool $dryRun = false): PruneResult
    {
        if ($days < 1) {
            throw new \InvalidArgumentException('days must be at least 1');
        }

        $before = new \DateTimeImmutable(sprintf('-%d days', $days));
        $connection = $this->entityManager->getConnection();
        $table = $this->entityManager->getClassMetadata(Job::class)->getTableName();
        $where = 'status IN (:statuses) AND created_at < :before';
        $params = ['statuses' => self::FINISHED_STATUSES, 'before' => $before];
        $types = ['statuses' => ArrayParameterType::STRING, 'before' => Types::DATETIME_IMMUTABLE];

        if ($dryRun) {
            $count = (int) $connection->fetchOne("SELECT COUNT(*) FROM $table WHERE $where", $params, $types);

            return new PruneResult($days, $before, $count, true);
        }

        $deleted = 0;
        do {
            $ids = $connection->fetchFirstColumn(
                sprintf('SELECT id FROM %s WHERE %s ORDER BY created_at LIMIT %d', $table, $where, self::BATCH_SIZE),
                $params,
                $types
            );
            if ($ids !== []) {
                $deleted += (int) $connection->executeStatement(
                    "DELETE FROM $table WHERE id IN (:ids)",
                    ['ids' => $ids],
                    ['ids' => ArrayParameterType::STRING]
                );
            }
        } while (\count($ids) === self::BATCH_SIZE);

        return new PruneResult($days, $before, $deleted, false);
    }
}
