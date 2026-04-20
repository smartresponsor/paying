<?php

declare(strict_types=1);

namespace App\Paying\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Exercises the doctrine schema reset scenario within the payment support test surface.
 */
final class DoctrineSchemaReset
{
    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    /**
     * Implements the reset behavior required by the local test double or scenario helper.
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function reset(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if ([] === $metadata) {
            return;
        }

        $connection = $entityManager->getConnection();
        $entityManager->clear();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $tool = new SchemaTool($entityManager);

        try {
            $tool->dropSchema($metadata);
        } catch (\Throwable) {
            // Fresh SQLite files and first-run test databases have nothing to drop.
        }

        $tool->createSchema($metadata);

        $connection->executeStatement('DROP TABLE IF EXISTS payment_idempotency');
        $connection->executeStatement(
            'CREATE TABLE payment_idempotency ('
            .'key VARCHAR(80) PRIMARY KEY NOT NULL, '
            .'value CLOB NOT NULL, '
            .'expires_at DATETIME NOT NULL'
            .')'
        );
    }
}
