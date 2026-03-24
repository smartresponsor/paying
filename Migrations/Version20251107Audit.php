<?php
// Marketing America Corp. Oleksandr Tishchenko
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Audit extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment_audit table (Postgres)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_audit (id SERIAL PRIMARY KEY, action VARCHAR(80) NOT NULL, context JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_audit');
    }
}
