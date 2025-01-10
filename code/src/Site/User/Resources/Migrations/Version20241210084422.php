<?php

declare(strict_types=1);

namespace Site\User\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241210084422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id BINARY(16) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(64) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            avatar TEXT DEFAULT NULL,
            password VARCHAR(255) DEFAULT NULL,
            password_token VARCHAR(1024) DEFAULT NULL,
            facebook_token VARCHAR(1024) DEFAULT NULL,
            google_token VARCHAR(1024) DEFAULT NULL,
            location JSON NOT NULL,
            status SMALLINT DEFAULT 1,
            phone_verified boolean default 0 not null
            email_verified boolean default 0 not null
            roles JSON DEFAULT NULL,
            referral TEXT DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_EMAIL (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS users');
    }
}
