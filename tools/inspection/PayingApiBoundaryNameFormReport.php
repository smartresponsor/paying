<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$legacy = [
    'src/Service/ApiErrorResponseFactory.php',
    'src/Service/ApiJsonBodyDecoder.php',
    'src/Service/ApiRequestValidator.php',
    'src/Service/OidcJwksCache.php',
    'src/Service/TokenVerifier.php',
    'src/Service/ValidationErrorMapper.php',
    'src/ServiceInterface/ApiErrorResponseFactoryInterface.php',
    'src/ServiceInterface/ApiJsonBodyDecoderInterface.php',
    'src/ServiceInterface/ApiRequestValidatorInterface.php',
    'src/ServiceInterface/OidcJwksCacheInterface.php',
    'src/ServiceInterface/TokenVerifierInterface.php',
    'src/ServiceInterface/ValidationErrorMapperInterface.php',
];

$required = [
    'src/Service/PaymentApiErrorResponseFactory.php',
    'src/Service/PaymentApiJsonBodyDecoder.php',
    'src/Service/PaymentApiRequestValidator.php',
    'src/Service/PaymentOidcJwksCache.php',
    'src/Service/PaymentTokenVerifier.php',
    'src/Service/PaymentValidationErrorMapper.php',
    'src/ServiceInterface/PaymentApiErrorResponseFactoryInterface.php',
    'src/ServiceInterface/PaymentApiJsonBodyDecoderInterface.php',
    'src/ServiceInterface/PaymentApiRequestValidatorInterface.php',
    'src/ServiceInterface/PaymentOidcJwksCacheInterface.php',
    'src/ServiceInterface/PaymentTokenVerifierInterface.php',
    'src/ServiceInterface/PaymentValidationErrorMapperInterface.php',
];

$violations = [];

foreach ($legacy as $path) {
    if (is_file($root.'/'.$path)) {
        $violations[] = 'Legacy unprefixed API/security boundary still exists: '.$path;
    }
}

foreach ($required as $path) {
    if (!is_file($root.'/'.$path)) {
        $violations[] = 'Required Payment-prefixed API/security boundary is missing: '.$path;
    }
}

$scanRoots = [
    $root.'/src',
    $root.'/tests',
    $root.'/config',
];

$forbiddenTokens = [
    'ApiErrorResponseFactoryInterface',
    'ApiJsonBodyDecoderInterface',
    'ApiRequestValidatorInterface',
    'ValidationErrorMapperInterface',
    'TokenVerifierInterface',
    'OidcJwksCacheInterface',
];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        if (!preg_match('/\.(php|ya?ml)$/', $path)) {
            continue;
        }

        $relative = str_replace($root.'/', '', $path);
        $content = (string) file_get_contents($path);

        foreach ($forbiddenTokens as $token) {
            if (str_contains($content, '\\'.$token) || preg_match('/\b'.$token.'\b/', $content)) {
                if (!str_contains($content, 'Payment'.$token)) {
                    $violations[] = 'Unprefixed token '.$token.' remains in '.$relative;
                }
            }
        }
    }
}

if ([] !== $violations) {
    fwrite(STDERR, "Paying API boundary nameEntity-form report: FAILED\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - '.$violation."\n");
    }

    exit(1);
}

echo "Paying API boundary nameEntity-form report: OK\n";
