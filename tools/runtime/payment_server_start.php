<?php

declare(strict_types=1);

$projectDir = dirname(__DIR__, 2);
$pidDir = $projectDir.'/var/run';
$pidFile = $pidDir.'/payment-local-server.pid';
$portFile = $pidDir.'/payment-local-server.port';
$modeFile = $pidDir.'/payment-local-server.mode';
$logFile = sys_get_temp_dir().'/paying-local-server.log';

if (!is_dir($pidDir) && !mkdir($pidDir, 0777, true) && !is_dir($pidDir)) {
    fwrite(STDERR, "Failed to create var/run.\n");
    exit(1);
}

$existingPid = is_file($pidFile) ? (int) trim((string) file_get_contents($pidFile)) : 0;
if ($existingPid > 0 && function_exists('posix_kill') && @posix_kill($existingPid, 0)) {
    fwrite(STDOUT, "Local server already running with PID {$existingPid}.\n");
    exit(0);
}

$mustUseDocker = 'docker' === (string) ($_ENV['PAYMENT_SERVER_RUNTIME'] ?? $_SERVER['PAYMENT_SERVER_RUNTIME'] ?? '')
    || !extension_loaded('pdo_pgsql')
    || !extension_loaded('pdo_sqlite')
    || (is_dir($projectDir.'/var/cache/dev') && !is_writable($projectDir.'/var/cache/dev'));

if ($mustUseDocker) {
    $command = ['docker', 'compose', 'up', '-d', 'app'];
    $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, $projectDir, $_ENV);
    if (!is_resource($process)) {
        fwrite(STDERR, "Failed to start Docker-backed local server.\n");
        exit(1);
    }

    $status = proc_close($process);
    if (0 !== $status) {
        exit($status);
    }

    file_put_contents($modeFile, "docker\n");
    file_put_contents($portFile, "8005\n");
    fwrite(STDOUT, "Started Docker-backed local server on http://127.0.0.1:8005.\n");
    exit(0);
}

$symfonyBinary = trim((string) shell_exec('command -v symfony 2>/dev/null'));
if ('' !== $symfonyBinary) {
    $port = (string) ($_ENV['PAYMENT_SERVER_PORT'] ?? $_SERVER['PAYMENT_SERVER_PORT'] ?? '8000');
    $command = [
        $symfonyBinary,
        'server:start',
        '--allow-http',
        '--no-tls',
        '--port='.$port,
        '-d',
    ];
    $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, $projectDir, $_ENV);
    if (!is_resource($process)) {
        fwrite(STDERR, "Failed to start Symfony local server.\n");
        exit(1);
    }

    $status = proc_close($process);
    exit(is_int($status) ? $status : 1);
}

$preferredPorts = array_unique(array_map(
    static fn (mixed $port): int => (int) $port,
    array_filter([
        $_ENV['PAYMENT_SERVER_PORT'] ?? $_SERVER['PAYMENT_SERVER_PORT'] ?? 8000,
        8001,
        8080,
    ]),
));

$selectedPort = null;
foreach ($preferredPorts as $candidatePort) {
    $probe = @stream_socket_server('tcp://127.0.0.1:'.$candidatePort, $errno, $errorMessage);
    if (false === $probe) {
        continue;
    }

    fclose($probe);
    $selectedPort = $candidatePort;
    break;
}

if (null === $selectedPort) {
    fwrite(STDERR, "Unable to find a free local server port.\n");
    exit(1);
}

$command = sprintf(
    'cd %s && nohup %s -S 127.0.0.1:%d -t public public/index.php > %s 2>&1 & echo $!',
    escapeshellarg($projectDir),
    escapeshellarg(PHP_BINARY),
    $selectedPort,
    escapeshellarg($logFile)
);

$pid = trim((string) shell_exec($command));
if ('' === $pid || !ctype_digit($pid)) {
    fwrite(STDERR, "Failed to start PHP built-in server.\n");
    exit(1);
}

file_put_contents($pidFile, $pid.PHP_EOL);
file_put_contents($portFile, (string) $selectedPort.PHP_EOL);
file_put_contents($modeFile, "local\n");
fwrite(STDOUT, "Started local PHP server on http://127.0.0.1:{$selectedPort} (PID {$pid}).\n");
