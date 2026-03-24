<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Data extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment, payment_transaction, payment_refund and payment_outbox_message tables aligned with App-owned payment lifecycle storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment (id VARCHAR(26) NOT NULL, status VARCHAR(16) NOT NULL, amount NUMERIC(14,2) NOT NULL, currency VARCHAR(3) NOT NULL, provider_ref VARCHAR(128) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_payment_id ON payment (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_status_updated_at ON payment (status, updated_at)');

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_transaction (id VARCHAR(36) NOT NULL, payment_id VARCHAR(26) NOT NULL, gateway_transaction_id VARCHAR(64) NOT NULL, type VARCHAR(16) NOT NULL, amount_minor INT NOT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_transaction_payment_id ON payment_transaction (payment_id)');
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_refund (id VARCHAR(36) NOT NULL, payment_id VARCHAR(26) NOT NULL, amount_minor INT NOT NULL, currency VARCHAR(3) NOT NULL, reason VARCHAR(64) DEFAULT NULL, refunded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_refund_payment_id ON payment_refund (payment_id)');
        $this->addSql("CREATE TABLE IF NOT EXISTS payment_outbox_message (id VARCHAR(36) NOT NULL, type VARCHAR(128) NOT NULL, payload JSON NOT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'pending', attempts INT NOT NULL DEFAULT 0, last_error TEXT DEFAULT NULL, routing_key VARCHAR(128) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_outbox_message_status_occurred_at ON payment_outbox_message (status, occurred_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_outbox_message');
        $this->addSql('DROP TABLE IF EXISTS payment_refund');
        $this->addSql('DROP TABLE IF EXISTS payment_transaction');
        $this->addSql('DROP TABLE IF EXISTS payment');
    }
}
