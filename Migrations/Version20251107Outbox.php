<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Outbox extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment_outbox and payment_dlq tables (Postgres)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_outbox (id SERIAL PRIMARY KEY, topic VARCHAR(120) NOT NULL, payload JSON NOT NULL, status VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_dlq (id SERIAL PRIMARY KEY, outbox_id INT NOT NULL, topic VARCHAR(120) NOT NULL, payload JSON NOT NULL, reason VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_dlq');
        $this->addSql('DROP TABLE IF EXISTS payment_outbox');
    }
}
