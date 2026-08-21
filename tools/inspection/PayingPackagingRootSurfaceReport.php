<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

$requiredRootFiles = [
    'composer.json',
    'phpunit.xml.dist',
    'README.md',
];

$requiredRootDirectories = [
    'config',
    'deploy',
    'docs',
    'public',
    'src',
    'tests',
    'tools',
];

$requiredPackagingPaths = [
    'deploy/docker',
    'docs/architecture',
    'tools/inspection',
];

$forbiddenLooseRootArtifacts = [
    'config-services.yaml',
    'services.yaml',
    'task.txt',
    'touched.txt',
];

$forbiddenRootDirectories = [
    'docker',
    'deployment',
    'scripts',
    'tmp',
    'node_modules',
];

$localGeneratedDirectories = [
    'var',
    'vendor',
];

$requiredReportScripts = [
    'report:release-candidate-structure',
    'report:canonical-structure-closure',
    'report:test-canonical-closure',
    'report:canonical-nameEntity-form',
];

$errors = [];
$warnings = [];

foreach ($requiredRootFiles as $file) {
    if (!is_file(paying_path($root, $file))) {
        $errors[] = 'Missing required root file: ' . $file;
    }
}

foreach ($requiredRootDirectories as $directory) {
    if (!is_dir(paying_path($root, $directory))) {
        $errors[] = 'Missing required root directory: ' . $directory;
    }
}

foreach ($requiredPackagingPaths as $path) {
    if (!file_exists(paying_path($root, $path))) {
        $errors[] = 'Missing required packaging path: ' . $path;
    }
}

foreach ($forbiddenLooseRootArtifacts as $file) {
    if (file_exists(paying_path($root, $file))) {
        $errors[] = 'Forbidden loose root artifact still present: ' . $file;
    }
}

foreach ($forbiddenRootDirectories as $directory) {
    if (is_dir(paying_path($root, $directory))) {
        $errors[] = 'Forbidden root-level directory still present: ' . $directory;
    }
}

foreach ($localGeneratedDirectories as $directory) {
    if (is_dir(paying_path($root, $directory))) {
        $warnings[] = 'Local generated directory present and ignored by packaging guard: ' . $directory;
    }
}

$composerFile = paying_path($root, 'composer.json');
$composer = is_file($composerFile) ? json_decode((string) file_get_contents($composerFile), true) : null;
$scripts = is_array($composer) && isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

foreach ($requiredReportScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $errors[] = 'Missing required composer report script: ' . $scriptName;
    }
}

echo "Paying packaging/root surface report\n";
echo "====================================\n";
echo 'Required root files: ' . count($requiredRootFiles) . "\n";
echo 'Required root directories: ' . count($requiredRootDirectories) . "\n";
echo 'Required packaging paths: ' . count($requiredPackagingPaths) . "\n";
echo 'Forbidden loose root artifacts: ' . count($forbiddenLooseRootArtifacts) . "\n";
echo 'Forbidden root directories: ' . count($forbiddenRootDirectories) . "\n";
echo 'Ignored local generated directories: ' . count($localGeneratedDirectories) . "\n";
echo 'Required report scripts: ' . count($requiredReportScripts) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
