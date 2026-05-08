<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function payment_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function payment_write_utf8_no_bom(string $path, string $contents): void
{
    file_put_contents($path, $contents);
}

function payment_reformat_composer(string $root): void
{
    $composerPath = payment_path($root, 'composer.json');
    $contents = (string) file_get_contents($composerPath);

    if (str_starts_with($contents, "\xEF\xBB\xBF")) {
        $contents = substr($contents, 3);
    }

    $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    $encoded = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    payment_write_utf8_no_bom($composerPath, $encoded . "\n");
    echo "Reformatted composer.json as stable UTF-8 without BOM.\n";
}

function payment_replace_class_name(string $root, string $relativePath, string $from, string $to): void
{
    $path = payment_path($root, $relativePath);

    if (!is_file($path)) {
        echo "Skipped missing file: {$relativePath}\n";
        return;
    }

    $contents = (string) file_get_contents($path);
    $updated = preg_replace(
        '/\bclass\s+' . preg_quote($from, '/') . '\b/',
        'class ' . $to,
        $contents,
        1
    );

    if (!is_string($updated) || $updated === $contents) {
        echo "No class-name replacement needed in {$relativePath}.\n";
        return;
    }

    payment_write_utf8_no_bom($path, $updated);
    echo "Updated test class name in {$relativePath}: {$from} -> {$to}\n";
}

function payment_render_reflection_type(?ReflectionType $type): string
{
    if ($type === null) {
        return '';
    }

    if ($type instanceof ReflectionNamedType) {
        $name = $type->getName();

        if (!$type->isBuiltin() && !in_array($name, ['self', 'static', 'parent'], true)) {
            $name = '\\' . $name;
        }

        if ($type->allowsNull() && !in_array($name, ['mixed', 'null'], true) && !str_starts_with($name, '?')) {
            return '?' . $name;
        }

        return $name;
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

    $name = '';

    if ($parameter->isPassedByReference()) {
        $name .= '&';
    }

    if ($parameter->isVariadic()) {
        $name .= '...';
    }

    $name .= '$' . $parameter->getName();

    $parts[] = $name . payment_render_default_value($parameter);

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

    for ($i = $openBraceOffset; $i < $length; ++$i) {
        $char = $contents[$i];

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

function payment_patch_repository_stub(string $root): void
{
    $relativePath = 'tests/Unit/PaymentConsoleFinalizeHandlerTest.php';
    $path = payment_path($root, $relativePath);

    if (!is_file($path)) {
        throw new RuntimeException('Missing expected unit test file: ' . $relativePath);
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

    $contents = (string) file_get_contents($path);

    if (preg_match('/new\s+class(?:\s*\([^)]*\))?\s+implements\s+PaymentRepositoryInterface\b/', $contents, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        throw new RuntimeException('Could not find anonymous PaymentRepositoryInterface stub in ' . $relativePath);
    }

    $matchOffset = $matches[0][1];
    $matchEnd = $matchOffset + strlen($matches[0][0]);
    $openBrace = strpos($contents, '{', $matchEnd);

    if ($openBrace === false) {
        throw new RuntimeException('Could not find anonymous repository stub opening brace in ' . $relativePath);
    }

    $closeBrace = payment_find_matching_brace($contents, $openBrace);
    $body = substr($contents, $openBrace + 1, $closeBrace - $openBrace - 1);

    $reflection = new ReflectionClass($interface);
    $missingStubs = [];

    foreach ($reflection->getMethods() as $method) {
        if (!$method->isPublic()) {
            continue;
        }

        if (preg_match('/\bfunction\s+' . preg_quote($method->getName(), '/') . '\s*\(/', $body) === 1) {
            continue;
        }

        $missingStubs[] = payment_render_method_stub($method);
    }

    if ($missingStubs === []) {
        echo "Anonymous PaymentRepositoryInterface stub already implements all public methods.\n";
        return;
    }

    $insertion = "\n" . implode("\n", $missingStubs);
    $updated = substr($contents, 0, $closeBrace) . $insertion . substr($contents, $closeBrace);

    payment_write_utf8_no_bom($path, $updated);
    echo 'Added missing PaymentRepositoryInterface stub methods in ' . $relativePath . ': ' . count($missingStubs) . "\n";
}

payment_reformat_composer($root);

payment_replace_class_name(
    $root,
    'tests/Unit/PaymentProjectionLagServiceTest.php',
    'ProjectionLagServiceTest',
    'PaymentProjectionLagServiceTest'
);

payment_replace_class_name(
    $root,
    'tests/Unit/PaymentRefundServiceTest.php',
    'RefundServiceTest',
    'PaymentRefundServiceTest'
);

payment_patch_repository_stub($root);

echo "Payment runtime test hotfix wave36 completed.\n";
