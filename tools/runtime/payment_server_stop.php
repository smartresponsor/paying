<?php

declare(strict_types=1);

require_once __DIR__.'/payment_runtime.php';

$projectDir = dirname(__DIR__, 2);
$pidFile = $projectDir.'/var/run/payment-local-server.pid';
$portFile = paymentResolvePortFile($projectDir);
$modeFile = paymentResolveModeFile($projectDir);

$symfonyBinary = trim((string) shell_exec('command -v symfony 2>/dev/null'));
if ('' !== $symfonyBinary) {
    $status = paymentRunProcess([$symfonyBinary, 'server:stop'], $projectDir);
    exit(is_int($status) ? $status : 1);
}

$mode = is_file($modeFile) ? trim((string) file_get_contents($modeFile)) : '';
if ('docker' === $mode) {
    $status = paymentRunProcess(['docker', 'compose', 'stop', 'app'], $projectDir);
    @unlink($modeFile);
    @unlink($portFile);
    exit(is_int($status) ? $status : 1);
}

if (!is_file($pidFile)) {
    fwrite(STDOUT, "No local PHP server PID file found.\n");
    exit(0);
}

$pid = (int) trim((string) file_get_contents($pidFile));
if ($pid > 0 && function_exists('posix_kill')) {
    @posix_kill($pid, SIGTERM);
}

@unlink($pidFile);
@unlink($portFile);
@unlink($modeFile);
fwrite(STDOUT, "Stopped local PHP server.\n");
