<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\AdaptiveRoutingInterface;
use App\ServiceInterface\ProviderScorerInterface;

final readonly class AdaptiveRouting implements AdaptiveRoutingInterface
{
    public function __construct(
        private ProviderScorerInterface $scorer,
    ) {}

    public function plan(array $candidates, string $operation): array
    {
        $ranked = $this->scorer->rank($candidates, $operation);
        if ([] === $ranked) {
            throw new \RuntimeException('No providers');
        }

        $positiveSum = 0.0;
        foreach ($ranked as $row) {
            $positiveSum += max(0.0, $row['score']);
        }

        $weights = [];
        foreach ($ranked as $row) {
            $weight = $positiveSum > 0
                ? max(0.0, $row['score']) / $positiveSum
                : (1 / count($ranked));

            $weights[] = [
                'provider' => $row['provider'],
                'weight' => round($weight, 4),
                'score' => $row['score'],
                'available' => $row['available'],
            ];
        }

        return [
            'chosen' => $weights[0]['provider'],
            'weights' => $weights,
        ];
    }
}
