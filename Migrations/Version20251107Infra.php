<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Infra extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create infra.payment_projection table (MySQL)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_projection (id CHAR(26) NOT NULL, status VARCHAR(16) NOT NULL, amount DECIMAL(14,2) NOT NULL, currency CHAR(3) NOT NULL, provider_ref VARCHAR(128) NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_projection');
    }
}
