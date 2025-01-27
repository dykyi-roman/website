<?php

declare(strict_types=1);

namespace Profile\UserStatus\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250127153343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_statuses table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_statuses (
                id BIGINT AUTO_INCREMENT NOT NULL,
                user_id BINARY(16) NOT NULL,
                is_online TINYINT(1) DEFAULT 0 NOT NULL,
                last_online_at TIMESTAMP NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('CREATE INDEX idx_user_statuses_user_id ON user_statuses (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_statuses');
    }
}
