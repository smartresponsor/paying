<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface AdaptiveRoutingInterface
{
    /**
     * @param list<string> $candidates
     * @return array{chosen:string,weights:list<array{provider:string,weight:float,score:float,available:bool}>}
     */
    public function plan(array $candidates, string $operation): array;
}
