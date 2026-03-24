<?php
// Marketing America Corp. Oleksandr Tishchenko
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
        return 'Create infrastructure projection tables for payment read model and watermark storage';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('payment_projection')) {
            $table = $schema->createTable('payment_projection');
            $table->addColumn('id', 'string', ['length' => 26]);
            $table->addColumn('status', 'string', ['length' => 16]);
            $table->addColumn('amount', 'decimal', ['precision' => 14, 'scale' => 2]);
            $table->addColumn('currency', 'string', ['length' => 3]);
            $table->addColumn('provider_ref', 'string', ['length' => 128, 'notnull' => false]);
            $table->addColumn('updated_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['status'], 'idx_payment_projection_status');
            $table->addIndex(['updated_at'], 'idx_payment_projection_updated_at');
        }

        if (!$schema->hasTable('payment_projection_meta')) {
            $table = $schema->createTable('payment_projection_meta');
            $table->addColumn('name', 'string', ['length' => 64]);
            $table->addColumn('value', 'text');
            $table->setPrimaryKey(['name']);
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('payment_projection_meta')) {
            $schema->dropTable('payment_projection_meta');
        }

        if ($schema->hasTable('payment_projection')) {
            $schema->dropTable('payment_projection');
        }
    }
}
