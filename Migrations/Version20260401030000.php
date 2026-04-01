<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add order_id to payment and payment_projection and backfill missing values from payment identifiers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE payment ADD COLUMN IF NOT EXISTS order_id VARCHAR(128) DEFAULT NULL");
        $this->addSql("UPDATE payment SET order_id = id WHERE order_id IS NULL OR order_id = ''");
        $this->addSql("ALTER TABLE payment ALTER COLUMN order_id SET NOT NULL");
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_order_id ON payment (order_id)');

        $this->addSql("ALTER TABLE payment_projection ADD COLUMN IF NOT EXISTS order_id VARCHAR(128) DEFAULT NULL");
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_payment_projection_order_id ON payment_projection (order_id)');
        $this->addSql("UPDATE payment_projection pp SET order_id = p.order_id, provider_ref = p.provider_ref FROM payment p WHERE pp.id = p.id AND (pp.order_id IS NULL OR pp.order_id = '')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_payment_projection_order_id');
        $this->addSql('ALTER TABLE payment_projection DROP COLUMN IF EXISTS order_id');
        $this->addSql('DROP INDEX IF EXISTS idx_payment_order_id');
        $this->addSql('ALTER TABLE payment DROP COLUMN IF EXISTS order_id');
    }
}
