<?php

declare(strict_types=1);

namespace EventStorage\Resources\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250107071812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event (
            id BINARY(16) NOT NULL,
            model_id VARCHAR(255) NOT NULL,
            type VARCHAR(255) NOT NULL,
            payload JSON NOT NULL,
            occurred_on DATETIME NOT NULL,
            version SMALLINT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE INDEX idx_events_aggregate ON event (type, model_id)');
        $this->addSql('CREATE INDEX idx_events_occurred_on ON event (occurred_on)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS event');
    }
}
