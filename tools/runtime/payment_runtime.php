<?php

declare(strict_types=1);

/**
 * @return array{0: resource, 1: resource, 2: resource}
 */
function paymentDefaultDescriptorSpec(): array
{
    return [0 => STDIN, 1 => STDOUT, 2 => STDERR];
}

/**
 * @param list<string> $command
 * @param array<string, string>|null $env
 */
function paymentRunProcess(array $command, ?string $workingDirectory = null, ?array $env = null): int
{
    $process = proc_open(
        $command,
        paymentDefaultDescriptorSpec(),
        $pipes,
        $workingDirectory ?? getcwd(),
        $env ?? $_ENV,
    );

    if (!is_resource($process)) {
        return 1;
    }

    $status = proc_close($process);

    return is_int($status) ? $status : 1;
}

function paymentEnv(string $name): ?string
{
    return $_ENV[$name]
        ?? $_SERVER[$name]
        ?? (false !== getenv($name) ? (string) getenv($name) : null);
}

/**
 * @param list<string> $requiredExtensions
 */
function paymentShouldUseDockerRuntime(array $requiredExtensions, ?string $cacheDir = null, string $runtimeEnvVar = 'PAYMENT_PHP_RUNTIME'): bool
{
    $runtime = paymentEnv($runtimeEnvVar);
    if ('local' === $runtime) {
        return false;
    }

    if ('docker' === $runtime) {
        return true;
    }

    foreach ($requiredExtensions as $extension) {
        if (!extension_loaded($extension)) {
            return true;
        }
    }

    return null !== $cacheDir && is_dir($cacheDir) && !is_writable($cacheDir);
}

function paymentComposeFile(string $projectDir): string
{
    return $projectDir.'/compose.yml';
}

/**
 * @param list<string> $phpArgs
 * @return list<string>
 */
function paymentDockerPhpCommand(string $projectDir, string $target, array $phpArgs = []): array
{
    if (!is_file(paymentComposeFile($projectDir))) {
        throw new RuntimeException('Docker fallback requested, but compose.yml was not found.');
    }

    return array_merge(
        ['docker', 'compose', 'run', '--rm', '-T', '-e', 'PAYMENT_BOOTSTRAP_SCHEMA=0', 'app', 'php', $target],
        $phpArgs,
    );
}

function paymentResolveModeFile(string $projectDir): string
{
    return $projectDir.'/var/run/payment-local-server.mode';
}

function paymentResolvePortFile(string $projectDir): string
{
    return $projectDir.'/var/run/payment-local-server.port';
}

