<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$warnings = [];

$requiredFiles = [
    'src/Form/PaymentConsoleFinalizeType.php',
    'src/Form/PaymentConsoleRefundType.php',
    'src/Form/PaymentCreateType.php',
    'src/Form/PaymentFinalizeType.php',
    'src/Form/PaymentStartType.php',
    'src/Repository/PaymentRepository.php',
    'src/RepositoryInterface/PaymentRepositoryInterface.php',
    'src/Message/Command/PaymentCreateCommand.php',
    'src/Message/Command/PaymentRefundCommand.php',
    'src/Message/Event/PaymentCapturedEvent.php',
    'src/Message/Event/PaymentFailedEvent.php',
    'src/Message/Event/PaymentRefundedEvent.php',
    'src/Message/Event/PaymentTransportMessage.php',
    'src/Message/Handler/PaymentCreateHandler.php',
    'src/Message/Handler/PaymentRefundHandler.php',
    'src/Message/Handler/PaymentEventConsumer.php',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $relativePath)) {
        $errors[] = 'Missing canonical application-surface file: ' . $relativePath;
    }
}

$scanRules = [
    'src/Form' => static fn (string $className): bool => str_starts_with($className, 'Payment') && str_ends_with($className, 'Type'),
    'src/Repository' => static fn (string $className): bool => str_starts_with($className, 'Payment') && str_ends_with($className, 'Repository'),
    'src/RepositoryInterface' => static fn (string $className): bool => str_starts_with($className, 'Payment') && str_ends_with($className, 'RepositoryInterface'),
    'src/Message' => static fn (string $className): bool => str_starts_with($className, 'Payment') && (str_ends_with($className, 'Command') || str_ends_with($className, 'Event') || str_ends_with($className, 'Message') || str_ends_with($className, 'Handler') || str_ends_with($className, 'Consumer')),
];

foreach ($scanRules as $relativeDirectory => $acceptsClassName) {
    $directory = $root . DIRECTORY_SEPARATOR . $relativeDirectory;
    if (!is_dir($directory)) {
        $warnings[] = 'Optional directory is absent: ' . $relativeDirectory;
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            $errors[] = 'Unable to read PHP file: ' . $path;
            continue;
        }

        if (!preg_match('/\b(?:final\s+|readonly\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $matches)) {
            $warnings[] = 'Unable to detect class-like symbol in: ' . $path;
            continue;
        }

        $className = $matches[1];
        if (!$acceptsClassName($className)) {
            $errors[] = sprintf('Application surface class-name drift: %s declares %s', $path, $className);
        }

        if (str_starts_with($className, 'PaymentPayment')) {
            $errors[] = sprintf('Double-prefix drift: %s declares %s', $path, $className);
        }
    }
}

$forbiddenPaths = [
    'src/Form/CreateType.php',
    'src/Form/StartType.php',
    'src/Form/FinalizeType.php',
    'src/Form/RefundType.php',
    'src/Repository/Repository.php',
    'src/Message/Command/CreateCommand.php',
    'src/Message/Command/RefundCommand.php',
];

foreach ($forbiddenPaths as $relativePath) {
    if (is_file($root . DIRECTORY_SEPARATOR . $relativePath)) {
        $errors[] = 'Forbidden unprefixed application-surface path remains: ' . $relativePath;
    }
}

fwrite(STDOUT, "Paying application surface name-form report\n");
fwrite(STDOUT, str_repeat('=', 49) . "\n");
fwrite(STDOUT, 'Required files checked: ' . count($requiredFiles) . "\n");
fwrite(STDOUT, 'Warnings: ' . count($warnings) . "\n");
foreach ($warnings as $warning) {
    fwrite(STDOUT, '[WARN] ' . $warning . "\n");
}

if ($errors !== []) {
    fwrite(STDERR, 'Status: FAIL' . "\n");
    foreach ($errors as $error) {
        fwrite(STDERR, '[ERROR] ' . $error . "\n");
    }
    exit(1);
}

fwrite(STDOUT, 'Status: OK' . "\n");
