<?php

declare(strict_types=1);

$environment = [
    'APP_ENV' => 'dev',
    'APP_DEBUG' => '0',
    'APP_SECRET' => 'payment_playwright_secret',
    'DATABASE_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.data.sqlite',
    'INFRASTRUCTURE_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.infrastructure.sqlite',
    'OIDC_DISABLED' => '1',
];

foreach ($environment as $name => $value) {
    $_ENV[$name] = $_SERVER[$name] = $value;
    putenv($name.'='.$value);
}

require dirname(__DIR__, 2).'/public/index.php';

