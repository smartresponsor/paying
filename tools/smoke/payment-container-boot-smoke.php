<?php

declare(strict_types=1);

passthru(escapeshellarg(PHP_BINARY).' tools/ci/console-test-env.php lint:container', $exitCode);
exit($exitCode);
