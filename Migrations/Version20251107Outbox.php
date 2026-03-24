<?php
// Marketing America Corp. Oleksandr Tishchenko
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
        return 'Create payment_dlq table for unified payment_outbox_message retry and replay lifecycle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS payment_dlq (
            id SERIAL PRIMARY KEY,
            outbox_id VARCHAR(36) NOT NULL,
            topic VARCHAR(120) NOT NULL,
            payload JSON NOT NULL,
            reason VARCHAR(255) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )");
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_dlq_outbox_id ON payment_dlq (outbox_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_dlq');
        $this->addSql('DROP TABLE IF EXISTS payment_outbox');
    }
}
