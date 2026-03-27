<?php

declare(strict_types=1);

$projectDir = dirname(__DIR__, 2);
$pidFile = $projectDir.'/var/run/payment-local-server.pid';
$portFile = $projectDir.'/var/run/payment-local-server.port';
$modeFile = $projectDir.'/var/run/payment-local-server.mode';

$symfonyBinary = trim((string) shell_exec('command -v symfony 2>/dev/null'));
if ('' !== $symfonyBinary) {
    $command = [
        $symfonyBinary,
        'server:stop',
    ];
    $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, $projectDir, $_ENV);
    if (!is_resource($process)) {
        fwrite(STDERR, "Failed to stop Symfony local server.\n");
        exit(1);
    }

    $status = proc_close($process);
    exit(is_int($status) ? $status : 1);
}

$mode = is_file($modeFile) ? trim((string) file_get_contents($modeFile)) : '';
if ('docker' === $mode) {
    $command = ['docker', 'compose', 'stop', 'app'];
    $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, $projectDir, $_ENV);
    if (!is_resource($process)) {
        fwrite(STDERR, "Failed to stop Docker-backed local server.\n");
        exit(1);
    }

    $status = proc_close($process);
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
