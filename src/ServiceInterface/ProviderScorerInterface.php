<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface ProviderScorerInterface
{
    /**
     * @param list<string> $candidates
     * @return list<array{provider:string,score:float,available:bool,successRate:float,avgDurationMs:float}>
     */
    public function rank(array $candidates, string $operation): array;

    /**
     * @param list<string> $candidates
     */
    public function choose(array $candidates, string $operation): string;
}
