<?php

declare(strict_types=1);

$entityGroups = [
    'business' => glob('src/Entity/Business/*.php') ?: [],
    'operational' => glob('src/Entity/Operational/*.php') ?: [],
];

$entities = array_merge(...array_values($entityGroups));
if ([] === $entities) {
    fwrite(STDERR, 'No Paying Doctrine entities discovered under src/Entity/Business or src/Entity/Operational.' . PHP_EOL);
    exit(1);
}

$failures = [];
foreach ($entities as $entity) {
    $contents = (string) file_get_contents($entity);
    if (!str_contains($contents, '#[ORM\\Entity')) {
        $failures[] = $entity;
    }
}
if ($failures !== []) {
    fwrite(STDERR, 'Missing #[ORM\\Entity] in: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo sprintf(
    'PaymentEntity doctrine mapping smoke passed for %d entities (%d business, %d operational).%s',
    count($entities),
    count($entityGroups['business']),
    count($entityGroups['operational']),
    PHP_EOL,
);
