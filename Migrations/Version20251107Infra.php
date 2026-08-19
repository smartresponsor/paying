<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 *
 */
final class Version20251107Infra extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create infrastructure projection tables for payment read model and watermark storage';
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

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_projection (id VARCHAR(26) NOT NULL PRIMARY KEY, status VARCHAR(16) NOT NULL, amount NUMERIC(14,2) NOT NULL, currency VARCHAR(3) NOT NULL, provider_ref VARCHAR(128) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_projection_status ON payment_projection (status)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_projection_updated_at ON payment_projection (updated_at)');
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_projection_meta (name VARCHAR(64) NOT NULL PRIMARY KEY, value TEXT NOT NULL)');
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

        if ($schema->hasTable('payment_projection_meta')) {
            $schema->dropTable('payment_projection_meta');
        }

        if ($schema->hasTable('payment_projection')) {
            $schema->dropTable('payment_projection');
        }
    }
}
