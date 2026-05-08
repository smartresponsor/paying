<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

$composerPath = paying_path($root, 'composer.json');
$composer = [];

if (is_file($composerPath)) {
    $contents = (string) file_get_contents($composerPath);
    if (!str_starts_with($contents, "\xEF\xBB\xBF")) {
        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                $composer = $decoded;
            }
        } catch (Throwable) {
            $composer = [];
        }
    }
}

$scripts = isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

$milestones = [
    'RC-2 canonical readiness' => array_key_exists('report:paying-canonical-readiness', $scripts),
    'RC-3 runtime issue inventory' => array_key_exists('report:runtime-issue-inventory', $scripts),
    'RC-3 runtime proof closure' => array_key_exists('report:runtime-proof-closure', $scripts),
    'RC-3 runtime proof core' => array_key_exists('report:runtime-proof-core', $scripts),
    'RC-3 milestone marker' => array_key_exists('report:rc3-milestone', $scripts),
];

$nextCommands = [
    'composer dump-autoload',
    'composer report:rc3-milestone',
    'composer report:composer-script-hygiene',
    'composer report:runtime-proof-closure',
    'composer report:runtime-proof-core',
];

echo "Paying RC-3 handoff memo\n";
echo "========================\n";
echo "Component: Paying\n";
echo "Namespace: App\\\\Paying\\\\\n";
echo "Milestone: RC-3 runtime proof surface assembled\n";
echo "Production code changed by this report: no\n\n";

echo "Milestone surface:\n";
foreach ($milestones as $label => $present) {
    echo '- ' . ($present ? '[OK] ' : '[MISSING] ') . $label . "\n";
}

echo "\nRecommended verification commands:\n";
foreach ($nextCommands as $command) {
    echo '- ' . $command . "\n";
}

$missing = array_keys(array_filter($milestones, static fn (bool $present): bool => !$present));

echo "\n";
if ($missing !== []) {
    echo "Status: FAILED\n";
    foreach ($missing as $label) {
        echo '- Missing milestone surface: ' . $label . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
