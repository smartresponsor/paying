<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface;

interface PaymentProviderVocabularyServiceInterface
{
    /** @return list<array{code: string, label: string, available: bool}> */
    public function list(): array;
}
