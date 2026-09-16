<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface;

interface PaymentMethodVocabularyServiceInterface
{
    /** @return list<array{code: string, label: string}> */
    public function list(string $providerCode): array;
}
