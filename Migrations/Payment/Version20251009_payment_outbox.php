<?php
declare(strict_types=1);
namespace OrderComponent\Payment\Migrations\Payment;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251009_payment_outbox extends AbstractMigration
{
    public function getDescription(): string
    { return 'Create payment_outbox_message table'; }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE payment_outbox_message (
            id UUID NOT NULL,
            type VARCHAR(128) NOT NULL,
            payload JSON NOT NULL,
            occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT ''pending'',
            attempts INT NOT NULL DEFAULT 0,
            last_error TEXT DEFAULT NULL,
            routing_key VARCHAR(128) DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_payment_outbox_status ON payment_outbox_message (status)');
    }

    public function down(Schema $schema): void
    { $this->addSql('DROP TABLE payment_outbox_message'); }
}
