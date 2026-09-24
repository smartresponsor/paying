<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923234500ObjectingIdentityConstraintNames extends AbstractMigration
{
    /** @var array<string, string> */
    private const array INDEX_RENAMES = [
        'uniq_db7d395989d9b62' => 'uniq_payment_gateway_slug',
        'uniq_7b61a1f6989d9b62' => 'uniq_payment_method_slug',
        'uniq_aab592a2989d9b62' => 'uniq_payment_outbox_message_slug',
        'uniq_2f452dc5d17f50a6' => 'uniq_payment_recurring_uuid',
        'uniq_2f452dc5989d9b62' => 'uniq_payment_recurring_slug',
        'uniq_7e148fce989d9b62' => 'uniq_payment_refund_slug',
        'uniq_87e9789d17f50a6' => 'uniq_payment_token_uuid',
        'uniq_87e9789989d9b62' => 'uniq_payment_token_slug',
        'uniq_84bbd50b989d9b62' => 'uniq_payment_transaction_slug',
        'uniq_42e595b5d17f50a6' => 'uniq_payment_translation_uuid',
        'uniq_42e595b5989d9b62' => 'uniq_payment_translation_slug',
        'uniq_85e992a2989d9b62' => 'uniq_payment_webhook_log_slug',
    ];

    public function getDescription(): string
    {
        return 'Rename Paying Doctrine hash unique indexes to deterministic semantic names, including Objecting identity indexes.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Paying unique index naming reconciliation supports PostgreSQL only.',
        );

        foreach (self::INDEX_RENAMES as $legacy => $canonical) {
            $this->addSql(sprintf(
                <<<'SQL'
DO $$
BEGIN
    IF to_regclass('public.%1$s') IS NOT NULL AND to_regclass('public.%2$s') IS NULL THEN
        EXECUTE 'ALTER INDEX %1$s RENAME TO %2$s';
    ELSIF to_regclass('public.%1$s') IS NOT NULL AND to_regclass('public.%2$s') IS NOT NULL THEN
        RAISE EXCEPTION 'Both legacy index %1$s and canonical index %2$s exist; manual reconciliation is required.';
    END IF;
END
$$
SQL,
                $legacy,
                $canonical,
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Deterministic Paying unique index naming is intentionally irreversible.',
        );
    }
}
