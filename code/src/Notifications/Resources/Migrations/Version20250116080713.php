<?php

declare(strict_types=1);

namespace Notifications\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116080713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_notifications table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_notifications (
            id BINARY(16) NOT NULL,
            user_id BINARY(16) NOT NULL,
            notification_id BINARY(16) NOT NULL,
            read_at TIMESTAMP NULL,
            deleted_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (notification_id) REFERENCES notifications(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_notifications');
    }
}
