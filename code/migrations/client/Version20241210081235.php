<?php

declare(strict_types=1);

namespace DoctrineMigrations\client;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241210081235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create client table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE client (
            id UUID NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(64) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            city VARCHAR(255) DEFAULT NULL,
            status SMALLINT DEFAULT 1,
            phone_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            activated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            deactivated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TRIGGER update_client_updated_at
            BEFORE UPDATE ON client
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS client');
    }
}
