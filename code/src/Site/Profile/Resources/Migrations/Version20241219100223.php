<?php

declare(strict_types=1);

namespace Site\Profile\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241219100223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create profile table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS profile');

        $this->addSql('CREATE TABLE profile (
            id BINARY(16) NOT NULL,
            `group` VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            value TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS profile');
    }
}
