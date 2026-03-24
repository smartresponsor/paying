<?php
// Marketing America Corp. Oleksandr Tishchenko
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107Circuit extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Payment circuit breaker storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_circuit (key VARCHAR(80) PRIMARY KEY, failure_count INT NOT NULL, retry_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_circuit');
    }
}
