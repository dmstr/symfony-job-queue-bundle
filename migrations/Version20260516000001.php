<?php
// file generated with AI assistance: Claude Code - 2026-07-01 14:45:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create the job table.
 *
 * Written against the DBAL Schema API (not raw platform SQL) so Doctrine emits
 * the correct DDL for whatever platform the consuming app runs on — MySQL,
 * PostgreSQL or SQLite. See MigrationsPortabilityTest.
 */
final class Version20260516000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create job table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('job');
        $table->addColumn('id', 'guid');
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->addColumn('status', 'string', ['length' => 20]);
        $table->addColumn('input_data', 'json');
        $table->addColumn('result_data', 'json', ['notnull' => false]);
        $table->addColumn('error_message', 'text', ['notnull' => false]);
        $table->addColumn('progress', 'integer', ['default' => 0]);
        $table->addColumn('started_at', 'datetime_immutable', ['notnull' => false]);
        $table->addColumn('completed_at', 'datetime_immutable', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime_immutable');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['type']);
        $table->addIndex(['status']);
        $table->addIndex(['created_at']);
        $table->addIndex(['type', 'status']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('job');
    }
}
