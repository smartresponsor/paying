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

function payment_complete_all_repository_stubs(string $root): void
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
    $pattern = '/new\s+class(?:\s*\([^)]*\))?\s+implements\s+PaymentRepositoryInterface\b/';

    if (preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE) < 1) {
        throw new RuntimeException('Could not find anonymous PaymentRepositoryInterface stubs in ' . $relativePath);
    }

    $reflection = new ReflectionClass($interface);
    $methods = array_filter(
        $reflection->getMethods(),
        static fn (ReflectionMethod $method): bool => $method->isPublic()
    );

    $patches = [];
    foreach ($matches[0] as $match) {
        $matchOffset = $match[1];
        $matchEnd = $matchOffset + strlen($match[0]);
        $openBrace = strpos($contents, '{', $matchEnd);

        if ($openBrace === false) {
            throw new RuntimeException('Could not find anonymous repository stub opening brace in ' . $relativePath);
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
        echo "All anonymous PaymentRepositoryInterface stubs already implement all public methods.\n";
        return;
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

    echo 'Completed anonymous PaymentRepositoryInterface stubs in ' . $relativePath . ': ' . count($patches) . " stub(s), {$totalAdded} method(s) added.\n";
}

payment_complete_all_repository_stubs($root);

echo "Payment finalize handler repository stub completion wave37 completed.\n";
