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
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        if ($schema->hasTable('payment_webhook_log')) {
            return;
        }

        $table = $schema->createTable('payment_webhook_log');
        $table->addColumn('id', 'guid');
        $table->addColumn('provider', 'string', ['length' => 32]);
        $table->addColumn('external_event_id', 'string', ['length' => 191]);
        $table->addColumn('payload', 'json');
        $table->addColumn('status', 'string', ['length' => 16]);
        $table->addColumn('duplicate_count', 'integer', ['default' => 0]);
        $table->addColumn('received_at', 'datetime_immutable');
        $table->addColumn('processed_at', 'datetime_immutable', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['provider', 'external_event_id'], 'uniq_payment_webhook_provider_event');
        $table->addIndex(['status'], 'idx_payment_webhook_status');
        $table->addIndex(['received_at'], 'idx_payment_webhook_received_at');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        if ($schema->hasTable('payment_webhook_log')) {
            $schema->dropTable('payment_webhook_log');
        }
    }
}
