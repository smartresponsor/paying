<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 *
 */
final class Version20251107Circuit extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Payment circuit breaker storage';
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS payment_circuit (key VARCHAR(80) PRIMARY KEY, failure_count INT NOT NULL, retry_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payment_circuit');
    }
}
