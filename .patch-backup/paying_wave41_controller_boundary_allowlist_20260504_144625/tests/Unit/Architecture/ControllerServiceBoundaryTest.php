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
