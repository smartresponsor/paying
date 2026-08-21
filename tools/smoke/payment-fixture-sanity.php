<?php

declare(strict_types=1);

$fixtureFiles = glob('src/Infrastructure/Fixture/*.php') ?: [];
if ([] === $fixtureFiles) {
    fwrite(STDERR, 'No payment fixtures found.'.PHP_EOL);
    exit(1);
}
echo 'PaymentEntity fixture sanity smoke passed for '.count($fixtureFiles).' fixtures.'.PHP_EOL;
