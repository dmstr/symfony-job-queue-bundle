<?php
// file generated with AI assistance: Claude Code - 2026-07-01 14:45:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prefix job with za7_ to disambiguate from tenant tables';
    }

    public function up(Schema $schema): void
    {
        // Portable across MySQL 8+, PostgreSQL and SQLite (unlike the MySQL-only
        // `RENAME TABLE`, which PostgreSQL does not understand).
        $this->addSql('ALTER TABLE job RENAME TO za7_job');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE za7_job RENAME TO job');
    }
}
