<?php
// file generated with AI assistance: Claude Code - 2026-06-10 13:00:00 UTC

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
        $this->addSql('RENAME TABLE job TO za7_job');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE za7_job TO job');
    }
}
