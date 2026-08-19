<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 *
 */
final class Version20251107Audit extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create payment_audit table (Postgres)';
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

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_audit (id SERIAL PRIMARY KEY, action VARCHAR(80) NOT NULL, context JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
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

        $this->addSql('DROP TABLE IF EXISTS payment_audit');
    }
}
