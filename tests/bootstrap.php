<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;

require dirname(__DIR__).'/vendor/autoload.php';

$projectDir = dirname(__DIR__);

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');

$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';
putenv('APP_DEBUG=1');

$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = 'sqlite:///'.$projectDir.'/var/payment.test.data.sqlite';
putenv('DATABASE_URL='.$_SERVER['DATABASE_URL']);

$_SERVER['INFRA_URL'] = $_ENV['INFRA_URL'] = 'sqlite:///'.$projectDir.'/var/payment.test.infra.sqlite';
putenv('INFRA_URL='.$_SERVER['INFRA_URL']);

require $projectDir.'/config/bootstrap.php';

$cacheDir = $projectDir.'/var/cache/test';
if (is_dir($cacheDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDir()) {
            @rmdir($fileInfo->getPathname());
            continue;
        }

        @unlink($fileInfo->getPathname());
    }

    @rmdir($cacheDir);
}

foreach ([
    $projectDir.'/var/payment.test.data.sqlite',
    $projectDir.'/var/payment.test.infra.sqlite',
] as $sqlitePath) {
    if (is_file($sqlitePath)) {
        @unlink($sqlitePath);
    }

    $dir = dirname($sqlitePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    touch($sqlitePath);
}

$kernel = new Kernel('test', true);
$kernel->boot();

$container = $kernel->getContainer();

if ($container->has('doctrine')) {
    $registry = $container->get('doctrine');

    if ($registry instanceof ManagerRegistry) {
        foreach ($registry->getManagers() as $entityManager) {
            if (!$entityManager instanceof EntityManagerInterface) {
                continue;
            }

            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            if ([] === $metadata) {
                continue;
            }

            $schemaTool = new SchemaTool($entityManager);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);

            $entityManager->clear();
        }
    }
}

$kernel->shutdown();
