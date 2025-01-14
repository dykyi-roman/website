<?php

declare(strict_types=1);

namespace Profile\Setting\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241219100223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create setting table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE settings (
            id BINARY(16) NOT NULL,
            user_id BINARY(16) NOT NULL,
            category VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            value TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_INDEX_USER_CATEGORY_NAME (`user_id`, `category`, `name`),
            CONSTRAINT FK_SETTINGS_USER_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_SETTINGS_USER_ID');
        $this->addSql('DROP TABLE IF EXISTS settings');
    }
}
