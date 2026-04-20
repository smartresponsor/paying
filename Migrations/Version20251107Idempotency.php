<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Idempotency extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment_idempotency table (Postgres)';
    }

    public function up(Schema $schema): void
    {
        if (method_exists(parent::class, __FUNCTION__)) {
            parent::up($schema);
        }

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_idempotency (key VARCHAR(80) PRIMARY KEY, value JSON NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        if (method_exists(parent::class, __FUNCTION__)) {
            parent::down($schema);
        }

        $this->addSql('DROP TABLE IF EXISTS payment_idempotency');
    }
}
