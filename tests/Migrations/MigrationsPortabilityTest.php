<?php
// file generated with AI assistance: Claude Code - 2026-07-01 14:45:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Migrations;

use Dmstr\SymfonyJobQueue\Migrations\Version20260516000001;
use Dmstr\SymfonyJobQueue\Migrations\Version20260608210000;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Regression guard: the migrations must run on a non-MySQL platform.
 *
 * We execute the real migration classes against an in-memory SQLite database
 * (a platform that is neither MySQL nor PostgreSQL). Any MySQL-only DDL
 * (CHAR(36) COMMENT, LONGTEXT, inline INDEX, ENGINE=InnoDB, `RENAME TABLE`, …)
 * would make SQLite throw, so a green test proves the DDL is portable — which is
 * what lets the bundle run on PostgreSQL.
 */
final class MigrationsPortabilityTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    }

    public function testMigrationsRunOnNonMysqlPlatform(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $logger = new NullLogger();
        $schema = new Schema();

        // Version20260516000001 — create job (Schema API).
        $create = new Version20260516000001($this->connection, $logger);
        $create->up($schema);
        foreach ($schema->toSql($platform) as $sql) {
            $this->connection->executeStatement($sql);
        }

        // Version20260608210000 — rename to za7_job (portable SQL).
        $rename = new Version20260608210000($this->connection, $logger);
        $rename->up($schema);
        foreach ($rename->getSql() as $query) {
            $this->connection->executeStatement($query->getStatement());
        }

        $sm = $this->connection->createSchemaManager();
        self::assertTrue($sm->tablesExist(['za7_job']), 'renamed table exists');
        self::assertFalse($sm->tablesExist(['job']), 'old table name is gone');

        // SQLite quotes reserved-word identifiers in the introspected key, so
        // normalise before comparing.
        $columns = array_map(
            static fn (string $name): string => trim($name, '"'),
            array_keys($sm->listTableColumns('za7_job'))
        );
        foreach (['id', 'type', 'status', 'input_data', 'result_data', 'error_message', 'progress', 'started_at', 'completed_at', 'created_at', 'updated_at'] as $column) {
            self::assertContains($column, $columns, sprintf('column %s exists', $column));
        }
    }
}
