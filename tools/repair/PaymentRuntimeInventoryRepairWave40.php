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
    echo "Reformatted composer.json as stable JSON and UTF-8 without BOM.\n";
}

function payment_rewrite_controller_service_boundary_test(string $root): void
{
    $path = payment_path($root, 'tests/Unit/Architecture/ControllerServiceBoundaryTest.php');

    if (!is_file($path)) {
        echo "Skipped missing ControllerServiceBoundaryTest.php.\n";
        return;
    }

    $contents = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class ControllerServiceBoundaryTest extends TestCase
{
    public function testControllersUseServiceInterfacesInsteadOfConcreteServices(): void
    {
        $controllerRoot = dirname(__DIR__, 3) . '/src/Controller';

        self::assertDirectoryExists($controllerRoot);

        $violations = [];

        foreach ($this->phpFiles($controllerRoot) as $file) {
            $relativePath = str_replace('\\', '/', substr($file, strlen(dirname(__DIR__, 3)) + 1));
            $tokens = token_get_all((string) file_get_contents($file));

            foreach ($this->importedClasses($tokens) as $import) {
                if (!str_starts_with($import, 'App\\Paying\\Service\\')) {
                    continue;
                }

                if (str_contains($import, '\\Dto\\') || str_contains($import, '\\Value\\')) {
                    continue;
                }

                if (str_ends_with($import, 'Interface')) {
                    continue;
                }

                $violations[] = $relativePath . ' imports concrete service ' . $import;
            }
        }

        self::assertSame([], $violations);
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item instanceof \SplFileInfo || !$item->isFile() || $item->getExtension() !== 'php') {
                continue;
            }

            $files[] = $item->getPathname();
        }

        sort($files);

        return $files;
    }

    /**
     * @param list<array|string> $tokens
     *
     * @return list<string>
     */
    private function importedClasses(array $tokens): array
    {
        $imports = [];
        $count = count($tokens);

        for ($index = 0; $index < $count; ++$index) {
            $token = $tokens[$index];

            if (!is_array($token) || $token[0] !== T_USE) {
                continue;
            }

            $import = '';
            for ($cursor = $index + 1; $cursor < $count; ++$cursor) {
                $current = $tokens[$cursor];

                if ($current === ';' || $current === ',') {
                    if ($import !== '') {
                        $imports[] = trim($import, '\\');
                    }

                    $import = '';

                    if ($current === ';') {
                        break;
                    }

                    continue;
                }

                if (is_array($current) && in_array($current[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                    $import .= $current[1];
                }
            }
        }

        return $imports;
    }
}

PHP;

    payment_write_utf8_no_bom($path, $contents);
    echo "Rewrote ControllerServiceBoundaryTest with token-based import parsing.\n";
}

payment_reformat_composer($root);
payment_rewrite_controller_service_boundary_test($root);

echo "Payment runtime inventory repair wave40 completed.\n";
