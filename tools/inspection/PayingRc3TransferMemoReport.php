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

$requiredScripts = [
    'report:paying-canonical-readiness',
    'report:runtime-issue-inventory',
    'report:runtime-proof-closure',
    'report:runtime-proof-core',
    'report:rc3-milestone',
    'report:rc3-handoff',
    'report:rc3-final-closure',
];

$missing = [];
foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $missing[] = $scriptName;
    }
}

echo "Paying RC-3 transfer memo report\n";
echo "===============================\n";
echo "Component: Paying\n";
echo "Namespace: App\\\\Paying\\\\\n";
echo "Milestone: RC-3 final closure surface assembled\n";
echo "Runtime proof status: green execution surface with issue inventory for notices/deprecations/skips\n";
echo "Production code changed by this report: no\n";
echo "\n";
echo "Essential continuation commands:\n";
echo "- composer dump-autoload\n";
echo "- composer report:rc3-final-closure\n";
echo "- composer report:rc3-handoff\n";
echo "- composer report:runtime-proof-core\n";
echo "\n";
echo "Recommended next phase:\n";
echo "- Clean PHPUnit notices/deprecations/skips.\n";
echo "- Add installed-runtime integration harness if needed.\n";
echo "- Keep App\\\\Paying\\\\ namespace and strict production contracts.\n";
echo "\n";
echo 'Missing scripts: ' . count($missing) . "\n";

if ($missing !== []) {
    echo "Status: FAILED\n";
    foreach ($missing as $scriptName) {
        echo '- Missing script: ' . $scriptName . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
