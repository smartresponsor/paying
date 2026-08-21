<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function payment_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function payment_relative(string $root, string $absolute): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedAbsolute = str_replace('\\', '/', $absolute);

    if (str_starts_with($normalizedAbsolute, $normalizedRoot)) {
        return substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    return $normalizedAbsolute;
}

function payment_write_utf8_no_bom(string $path, string $contents): void
{
    file_put_contents($path, $contents);
}

function payment_collect_test_php_files(string $root): array
{
    $directory = payment_path($root, 'tests');

    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile() || strtolower($item->getExtension()) !== 'php') {
            continue;
        }

        $files[] = $item->getPathname();
    }

    sort($files);

    return $files;
}

function payment_render_reflection_type(?ReflectionType $type): string
{
    if ($type === null) {
        return '';
    }

    if ($type instanceof ReflectionNamedType) {
        $nameEntity = $type->getName();

        if (!$type->isBuiltin() && !in_array($nameEntity, ['self', 'static', 'parent'], true)) {
            $nameEntity = '\\' . $nameEntity;
        }

        if ($type->allowsNull() && !in_array($nameEntity, ['mixed', 'null'], true) && !str_starts_with($nameEntity, '?')) {
            return '?' . $nameEntity;
        }

        return $nameEntity;
    }

    if ($type instanceof ReflectionUnionType) {
        $parts = [];
        foreach ($type->getTypes() as $namedType) {
            $parts[] = payment_render_reflection_type($namedType);
        }

        return implode('|', $parts);
    }

    if ($type instanceof ReflectionIntersectionType) {
        $parts = [];
        foreach ($type->getTypes() as $namedType) {
            $parts[] = payment_render_reflection_type($namedType);
        }

        return implode('&', $parts);
    }

    return '';
}

function payment_render_default_value(ReflectionParameter $parameter): string
{
    if (!$parameter->isDefaultValueAvailable()) {
        return '';
    }

    if ($parameter->isDefaultValueConstant()) {
        return ' = ' . $parameter->getDefaultValueConstantName();
    }

    return ' = ' . var_export($parameter->getDefaultValue(), true);
}

function payment_render_parameter(ReflectionParameter $parameter): string
{
    $parts = [];
    $type = payment_render_reflection_type($parameter->getType());

    if ($type !== '') {
        $parts[] = $type;
    }

    $nameEntity = '';

    if ($parameter->isPassedByReference()) {
        $nameEntity .= '&';
    }

    if ($parameter->isVariadic()) {
        $nameEntity .= '...';
    }

    $nameEntity .= '$' . $parameter->getName();

    $parts[] = $nameEntity . payment_render_default_value($parameter);

    return implode(' ', $parts);
}

function payment_render_method_stub(ReflectionMethod $method): string
{
    $parameters = array_map(
        static fn (ReflectionParameter $parameter): string => payment_render_parameter($parameter),
        $method->getParameters()
    );

    $returnType = payment_render_reflection_type($method->getReturnType());
    $return = $returnType === '' ? '' : ': ' . $returnType;

    return sprintf(
        "        public function %s(%s)%s\n        {\n            throw new \\LogicException('Test repository stub method is not configured: %s');\n        }\n",
        $method->getName(),
        implode(', ', $parameters),
        $return,
        $method->getName()
    );
}

function payment_find_matching_brace(string $contents, int $openBraceOffset): int
{
    $length = strlen($contents);
    $depth = 0;
    $inString = null;
    $escaped = false;

    for ($i = $openBraceOffset; $i < $length; ++$i) {
        $char = $contents[$i];

        if ($inString !== null) {
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === $inString) {
                $inString = null;
            }

            continue;
        }

        if ($char === '"' || $char === "'") {
            $inString = $char;
            continue;
        }

        if ($char === '{') {
            ++$depth;
            continue;
        }

        if ($char === '}') {
            --$depth;

            if ($depth === 0) {
                return $i;
            }
        }
    }

    throw new RuntimeException('Could not find matching closing brace for anonymous repository stub.');
}

function payment_complete_repository_stubs_in_file(string $root, string $path, array $methods): array
{
    $contents = (string) file_get_contents($path);

    if (!str_contains($contents, 'PaymentRepositoryInterface')) {
        return ['changed' => false, 'stubs' => 0, 'methods' => 0];
    }

    $pattern = '/new\s+class(?:\s*\([^)]*\))?\s+implements\s+(?:\\\\?App\\\\Paying\\\\RepositoryInterface\\\\)?PaymentRepositoryInterface\b/';

    if (preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE) < 1) {
        return ['changed' => false, 'stubs' => 0, 'methods' => 0];
    }

    $patches = [];

    foreach ($matches[0] as $match) {
        $matchOffset = $match[1];
        $matchEnd = $matchOffset + strlen($match[0]);
        $openBrace = strpos($contents, '{', $matchEnd);

        if ($openBrace === false) {
            throw new RuntimeException('Could not find anonymous repository stub opening brace in ' . payment_relative($root, $path));
        }

        $closeBrace = payment_find_matching_brace($contents, $openBrace);
        $body = substr($contents, $openBrace + 1, $closeBrace - $openBrace - 1);
        $missingStubs = [];

        foreach ($methods as $method) {
            if (preg_match('/\bfunction\s+' . preg_quote($method->getName(), '/') . '\s*\(/', $body) === 1) {
                continue;
            }

            $missingStubs[] = payment_render_method_stub($method);
        }

        if ($missingStubs === []) {
            continue;
        }

        $patches[] = [
            'offset' => $closeBrace,
            'insertion' => "\n" . implode("\n", $missingStubs),
            'count' => count($missingStubs),
        ];
    }

    if ($patches === []) {
        return ['changed' => false, 'stubs' => 0, 'methods' => 0];
    }

    usort(
        $patches,
        static fn (array $left, array $right): int => $right['offset'] <=> $left['offset']
    );

    $totalAdded = 0;
    foreach ($patches as $patch) {
        $contents = substr($contents, 0, $patch['offset']) . $patch['insertion'] . substr($contents, $patch['offset']);
        $totalAdded += $patch['count'];
    }

    payment_write_utf8_no_bom($path, $contents);

    return ['changed' => true, 'stubs' => count($patches), 'methods' => $totalAdded];
}

$autoload = payment_path($root, 'vendor/autoload.php');
if (!is_file($autoload)) {
    throw new RuntimeException('Missing vendor/autoload.php. Run composer install before this repair.');
}

require_once $autoload;

$interface = 'App\\Paying\\RepositoryInterface\\PaymentRepositoryInterface';
if (!interface_exists($interface)) {
    throw new RuntimeException('Interface not autoloadable: ' . $interface);
}

$reflection = new ReflectionClass($interface);
$methods = array_values(array_filter(
    $reflection->getMethods(),
    static fn (ReflectionMethod $method): bool => $method->isPublic()
));

$changedFiles = 0;
$totalStubs = 0;
$totalMethods = 0;

foreach (payment_collect_test_php_files($root) as $file) {
    $result = payment_complete_repository_stubs_in_file($root, $file, $methods);

    if (!$result['changed']) {
        continue;
    }

    ++$changedFiles;
    $totalStubs += $result['stubs'];
    $totalMethods += $result['methods'];

    echo 'Completed repository stub(s) in ' . payment_relative($root, $file) . ': ' . $result['stubs'] . ' stub(s), ' . $result['methods'] . " method(s) added.\n";
}

echo "Payment all test repository stubs completion wave38 completed.\n";
echo 'Changed files: ' . $changedFiles . "\n";
echo 'Completed stubs: ' . $totalStubs . "\n";
echo 'Added methods: ' . $totalMethods . "\n";
