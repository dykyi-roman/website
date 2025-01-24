<?php

declare(strict_types=1);

namespace Notifications\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116080712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notifications table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notifications (
            id BINARY(16) NOT NULL,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(100) NOT NULL,
            icon TEXT DEFAULT NULL,
            link TEXT DEFAULT NULL,
            title json NOT NULL,
            message json NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notifications');
    }
}
