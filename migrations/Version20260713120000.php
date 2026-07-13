<?php
// file generated with AI assistance: Claude Code - 2026-07-13 12:00:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713120000 extends AbstractMigration
{
    /**
     * Doctrine-generated index names from the original `job` table creation
     * (deterministic hash of table + columns), carried along unchanged by the
     * previous table renames. Replaced by explicit, rename-proof names that
     * match the entity mapping.
     */
    private const INDEX_RENAMES = [
        'IDX_FBD8E0F88CDE5729' => ['dmstr_job_type_idx', 'type'],
        'IDX_FBD8E0F87B00651C' => ['dmstr_job_status_idx', 'status'],
        'IDX_FBD8E0F88B8E8428' => ['dmstr_job_created_at_idx', 'created_at'],
        'IDX_FBD8E0F88CDE57297B00651C' => ['dmstr_job_type_status_idx', 'type, status'],
    ];

    public function getDescription(): string
    {
        return 'Rename za7_job to dmstr_job and give indexes explicit names (vendor prefix for bundle tables)';
    }

    public function up(Schema $schema): void
    {
        // Portable across MySQL 8+, PostgreSQL and SQLite (unlike the MySQL-only
        // `RENAME TABLE`, which PostgreSQL does not understand). InnoDB updates
        // foreign keys referencing the renamed table automatically.
        $this->addSql('ALTER TABLE za7_job RENAME TO dmstr_job');

        // Index-rename syntax is platform-specific, so branch: MySQL renames in
        // place, everything else drops and recreates (SQLite has no rename).
        $isMysql = $this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform;
        foreach (self::INDEX_RENAMES as $old => [$new, $columns]) {
            if ($isMysql) {
                $this->addSql(sprintf('ALTER TABLE dmstr_job RENAME INDEX %s TO %s', $old, $new));
            } else {
                $this->addSql(sprintf('DROP INDEX %s', $old));
                $this->addSql(sprintf('CREATE INDEX %s ON dmstr_job (%s)', $new, $columns));
            }
        }
    }

    public function down(Schema $schema): void
    {
        $isMysql = $this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform;
        foreach (self::INDEX_RENAMES as $old => [$new, $columns]) {
            if ($isMysql) {
                $this->addSql(sprintf('ALTER TABLE dmstr_job RENAME INDEX %s TO %s', $new, $old));
            } else {
                $this->addSql(sprintf('DROP INDEX %s', $new));
                $this->addSql(sprintf('CREATE INDEX %s ON dmstr_job (%s)', $old, $columns));
            }
        }

        $this->addSql('ALTER TABLE dmstr_job RENAME TO za7_job');
    }
}
