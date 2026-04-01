<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

require dirname(__DIR__, 2).'/config/bootstrap.php';

$appEnv = (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev');
$appDebug = filter_var($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? '1', FILTER_VALIDATE_BOOL);

$kernel = new Kernel($appEnv, $appDebug);
$kernel->boot();

$container = $kernel->getContainer();

/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get('doctrine')->getManager();
$schemaTool = new SchemaTool($entityManager);
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

if ([] !== $metadata) {
    $schemaTool->updateSchema($metadata, true);
}

/** @var Connection $data */
$data = $container->get('doctrine.dbal.data_connection');
/** @var Connection $infra */
$infra = $container->get('doctrine.dbal.infra_connection');

$connectionStatements = [
    [
        'connection' => $data,
        'sql' => [
        <<<'SQL'
CREATE TABLE IF NOT EXISTS payment_idempotency (
    key VARCHAR(80) PRIMARY KEY NOT NULL,
    value TEXT NOT NULL,
    expires_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
)
SQL,
        <<<'SQL'
CREATE TABLE IF NOT EXISTS payment_dlq (
    id SERIAL PRIMARY KEY,
    outbox_id VARCHAR(36) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
)
SQL,
        ],
    ],
    [
        'connection' => $infra,
        'sql' => [
        <<<'SQL'
CREATE TABLE IF NOT EXISTS payment_projection (
    id VARCHAR(36) PRIMARY KEY NOT NULL,
    amount VARCHAR(32) NOT NULL,
    currency VARCHAR(8) NOT NULL,
    status VARCHAR(32) NOT NULL,
    updated_at VARCHAR(32) NOT NULL
)
SQL,
        <<<'SQL'
CREATE TABLE IF NOT EXISTS payment_projection_meta (
    name VARCHAR(64) PRIMARY KEY NOT NULL,
    value VARCHAR(255) NOT NULL
)
SQL,
        ],
    ],
];

foreach ($connectionStatements as $item) {
    /** @var Connection $connection */
    $connection = $item['connection'];

    foreach ($item['sql'] as $sql) {
        $connection->executeStatement($sql);
    }
}

$kernel->shutdown();

fwrite(STDOUT, "Payment runtime schema bootstrap complete.\n");
