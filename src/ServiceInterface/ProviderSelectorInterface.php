<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface ProviderSelectorInterface
{
    /**
     * @param list<string> $candidates
     */
    public function choose(array $candidates): string;

    /**
     * @param list<string> $candidates
     * @return array<int, array{provider:string, available:bool, reason:string}>
     */
    public function explain(array $candidates): array;
}
