<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$targets = [
    $root.'/src',
    $root.'/tests',
];

$phpFiles = [];
foreach ($targets as $target) {
    if (!is_dir($target)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        if ('php' !== $file->getExtension()) {
            continue;
        }

        $phpFiles[] = $file->getPathname();
    }
}

sort($phpFiles);

$issues = [];
$summary = [
    'class_like' => ['total' => 0, 'documented' => 0],
    'public_method' => ['total' => 0, 'documented' => 0],
];

foreach ($phpFiles as $path) {
    $code = file_get_contents($path);
    if (false === $code) {
        fwrite(STDERR, 'Unable to read '.$path.PHP_EOL);
        exit(2);
    }

    $tokens = token_get_all($code, TOKEN_PARSE);
    $namespace = '';
    $lastDocBlock = null;
    $classDepthStack = [];
    $braceDepth = 0;

    $tokenCount = count($tokens);
    for ($i = 0; $i < $tokenCount; ++$i) {
        $token = $tokens[$i];

        if ('{' === $token) {
            ++$braceDepth;
            continue;
        }

        if ('}' === $token) {
            while ([] !== $classDepthStack && $braceDepth === end($classDepthStack)['depth']) {
                array_pop($classDepthStack);
            }
            --$braceDepth;
            $braceDepth = max($braceDepth, 0);
            continue;
        }

        if (!is_array($token)) {
            continue;
        }

        [$id, $text, $line] = $token;

        if (T_NAMESPACE === $id) {
            $namespace = collectName($tokens, $i + 1);
            $lastDocBlock = null;
            continue;
        }

        if (T_DOC_COMMENT === $id) {
            $lastDocBlock = ['text' => $text, 'line' => $line];
            continue;
        }

        if (in_array($id, [
            T_WHITESPACE,
            T_ATTRIBUTE,
            T_PUBLIC,
            T_PROTECTED,
            T_PRIVATE,
            T_STATIC,
            T_FINAL,
            T_ABSTRACT,
            T_READONLY,
            T_STRING,
            T_NAME_QUALIFIED,
            T_NAME_FULLY_QUALIFIED,
            T_NS_SEPARATOR,
            T_DOUBLE_COLON,
            T_CONSTANT_ENCAPSED_STRING,
            T_LNUMBER,
            T_DNUMBER,
            T_ARRAY,
        ], true)) {
            continue;
        }

        if (in_array($id, [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
            $nameEntity = nextIdentifier($tokens, $i + 1);
            if (null === $nameEntity) {
                $lastDocBlock = null;
                continue;
            }

            ++$summary['class_like']['total'];
            $fqcn = '' !== $namespace ? $namespace.'\\'.$nameEntity : $nameEntity;
            if (hasSemanticDocBlock($lastDocBlock)) {
                ++$summary['class_like']['documented'];
            } else {
                $issues[] = [
                    'type' => 'class_like',
                    'path' => relativePath($root, $path),
                    'line' => $line,
                    'nameEntity' => $fqcn,
                    'message' => 'Missing semantic DocBlock on class-like declaration.',
                ];
            }

            $classDepthStack[] = ['depth' => $braceDepth];
            $lastDocBlock = null;
            continue;
        }

        if (T_FUNCTION === $id && [] !== $classDepthStack) {
            $nameEntity = nextIdentifier($tokens, $i + 1);
            if (null === $nameEntity || '__construct' === $nameEntity || '__destruct' === $nameEntity) {
                $lastDocBlock = null;
                continue;
            }

            if (!isPublicMethod($tokens, $i - 1)) {
                $lastDocBlock = null;
                continue;
            }

            ++$summary['public_method']['total'];
            if (hasSemanticDocBlock($lastDocBlock)) {
                ++$summary['public_method']['documented'];
            } else {
                $issues[] = [
                    'type' => 'public_method',
                    'path' => relativePath($root, $path),
                    'line' => $line,
                    'nameEntity' => $nameEntity,
                    'message' => 'Missing semantic DocBlock on public method.',
                ];
            }

            $lastDocBlock = null;
            continue;
        }

        if (!in_array($id, [T_COMMENT], true)) {
            $lastDocBlock = null;
        }
    }
}

$classCoverage = coverage($summary['class_like']['documented'], $summary['class_like']['total']);
$methodCoverage = coverage($summary['public_method']['documented'], $summary['public_method']['total']);

echo sprintf(
    "Semantic DocBlock coverage\n- class-like: %d/%d (%.2f%%)\n- public methods: %d/%d (%.2f%%)\n",
    $summary['class_like']['documented'],
    $summary['class_like']['total'],
    $classCoverage,
    $summary['public_method']['documented'],
    $summary['public_method']['total'],
    $methodCoverage,
);

if ([] === $issues) {
    echo 'OK semantic DocBlock coverage is 100%.'.PHP_EOL;
    exit(0);
}

echo PHP_EOL.'First issues:'.PHP_EOL;
foreach (array_slice($issues, 0, 50) as $issue) {
    echo sprintf('- %s:%d %s [%s]%s', $issue['path'], $issue['line'], $issue['message'], $issue['nameEntity'], PHP_EOL);
}

exit(1);

function collectName(array $tokens, int $index): string
{
    $parts = [];
    $count = count($tokens);
    for ($i = $index; $i < $count; ++$i) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            if (';' === $token || '{' === $token) {
                break;
            }
            continue;
        }

        if (in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR], true)) {
            $parts[] = $token[1];
            continue;
        }

        if (T_WHITESPACE === $token[0]) {
            continue;
        }

        break;
    }

    return trim(implode('', $parts), '\\');
}

function nextIdentifier(array $tokens, int $index): ?string
{
    $count = count($tokens);
    for ($i = $index; $i < $count; ++$i) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            continue;
        }

        if (in_array($token[0], [T_STRING], true)) {
            return $token[1];
        }

        if (T_WHITESPACE === $token[0] || T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG === $token[0] || T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG === $token[0]) {
            continue;
        }

        return null;
    }

    return null;
}

function isPublicMethod(array $tokens, int $index): bool
{
    for ($i = $index; $i >= 0; --$i) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            if (';' === $token || '{' === $token || '}' === $token) {
                return false;
            }
            continue;
        }

        if (T_PUBLIC === $token[0]) {
            return true;
        }

        if (in_array($token[0], [T_PRIVATE, T_PROTECTED], true)) {
            return false;
        }

        if (in_array($token[0], [T_WHITESPACE, T_FINAL, T_STATIC, T_ABSTRACT, T_READONLY, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return false;
    }

    return false;
}

function hasSemanticDocBlock(?array $docBlock): bool
{
    if (null === $docBlock) {
        return false;
    }

    foreach (preg_split('/\R/', $docBlock['text']) as $line) {
        $normalized = trim($line, " \t\n\r\0\x0B/*");
        if ('' === $normalized) {
            continue;
        }

        if (str_starts_with($normalized, '@')) {
            continue;
        }

        return true;
    }

    return false;
}

function relativePath(string $root, string $path): string
{
    return ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
}

function coverage(int $documented, int $total): float
{
    if (0 === $total) {
        return 100.0;
    }

    return round(($documented / $total) * 100, 2);
}
