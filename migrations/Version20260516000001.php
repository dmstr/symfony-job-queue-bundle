<?php
// file generated with AI assistance: Claude Code - 2026-06-10 13:00:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create job table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE job (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                type VARCHAR(50) NOT NULL,
                status VARCHAR(20) NOT NULL,
                input_data JSON NOT NULL,
                result_data JSON DEFAULT NULL,
                error_message LONGTEXT DEFAULT NULL,
                progress INT DEFAULT 0 NOT NULL,
                started_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                completed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_FBD8E0F88CDE5729 (type),
                INDEX IDX_FBD8E0F87B00651C (status),
                INDEX IDX_FBD8E0F88B8E8428 (created_at),
                INDEX IDX_FBD8E0F88CDE57297B00651C (type, status),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE job');
    }
}
