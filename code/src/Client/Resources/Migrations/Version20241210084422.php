<?php

declare(strict_types=1);

namespace App\Client\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241210084422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create client table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS client');

        $this->addSql('CREATE TABLE client (
            id BINARY(16) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(64) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            city VARCHAR(255) DEFAULT NULL,
            status SMALLINT DEFAULT 1,
            phone_verified_at DATETIME DEFAULT NULL,
            email_verified_at DATETIME DEFAULT NULL,
            activated_at DATETIME DEFAULT NULL,
            deactivated_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS client');
    }
}
