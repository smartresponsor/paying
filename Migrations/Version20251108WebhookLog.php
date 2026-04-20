<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 *
 */
final class Version20251108WebhookLog extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create payment_webhook_log table for webhook idempotency and processing audit';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        if (method_exists(parent::class, __FUNCTION__)) {
            parent::up($schema);
        }

        if ($schema->hasTable('payment_webhook_log')) {
            return;
        }

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_webhook_log (id UUID NOT NULL PRIMARY KEY, provider VARCHAR(32) NOT NULL, external_event_id VARCHAR(191) NOT NULL, payload JSON NOT NULL, status VARCHAR(16) NOT NULL, duplicate_count INT NOT NULL DEFAULT 0, received_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_payment_webhook_provider_event ON payment_webhook_log (provider, external_event_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_webhook_status ON payment_webhook_log (status)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_webhook_received_at ON payment_webhook_log (received_at)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        if (method_exists(parent::class, __FUNCTION__)) {
            parent::down($schema);
        }

        if ($schema->hasTable('payment_webhook_log')) {
            $schema->dropTable('payment_webhook_log');
        }
    }
}
