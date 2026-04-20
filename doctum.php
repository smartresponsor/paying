<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

if (!class_exists(Doctum::class)) {
    return static function (): void {};
}

$root = __DIR__;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in([
        $root . '/src',
    ]);

return new Doctum($iterator, [
    'title' => 'Payment code reference',
    'build_dir' => $root . '/docs/generated/doctum',
    'cache_dir' => $root . '/var/cache/doctum',
    'default_opened_level' => 2,
]);
