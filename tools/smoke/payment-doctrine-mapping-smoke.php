<?php

declare(strict_types=1);

$entities = glob('src/Entity/*.php') ?: [];
$failures = [];
foreach ($entities as $entity) {
    $contents = (string)file_get_contents($entity);
    if (!str_contains($contents, '#[ORM\\Entity')) {
        $failures[] = $entity;
    }
}
if ($failures !== []) {
    fwrite(STDERR, 'Missing #[ORM\\Entity] in: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'Payment doctrine mapping smoke passed for ' . count($entities) . ' entities.' . PHP_EOL;
