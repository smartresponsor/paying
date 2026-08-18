<?php

declare(strict_types=1);

namespace App\Paying\Lifecycle;

/**
 * Canonical lifecycle transition policy for this component.
 *
 * It is intentionally independent from Doctrine so entities, services, forms,
 * and CLI importers can use the same transition guard.
 */
final class PaymentLifecyclePolicy
{
    /** @var array<string, list<string>> */
    private const ALLOWED = [
        'new' => ['pending', 'processing', 'canceled', 'failed'],
        'pending' => ['processing', 'authorized', 'canceled', 'failed'],
        'processing' => ['authorized', 'completed', 'failed', 'canceled'],
        'authorized' => ['captured', 'completed', 'canceled', 'failed'],
        'captured' => ['completed', 'partially_refunded', 'refunded', 'disputed'],
        'completed' => ['partially_refunded', 'refunded', 'disputed'],
        'partially_refunded' => ['refunded', 'disputed'],
        'disputed' => ['completed', 'refunded', 'failed'],
        'failed' => [],
        'canceled' => [],
        'refunded' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        return $from === $to || in_array($to, self::ALLOWED[$from] ?? [], true);
    }

    public static function assertCanTransition(string $from, string $to): void
    {
        if (!self::canTransition($from, $to)) {
            throw new \DomainException(sprintf('Invalid lifecycle transition from "%s" to "%s".', $from, $to));
        }
    }

    /** @return list<string> */
    public static function knownStates(): array
    {
        return array_values(array_unique(array_merge(array_keys(self::ALLOWED), ...array_values(self::ALLOWED))));
    }
}
