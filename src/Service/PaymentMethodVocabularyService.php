<?php

declare(strict_types=1);

namespace App\Paying\Service;

use App\Cataloging\ServiceInterface\CatalogCategoryTypeVocabularyServiceInterface;
use App\Paying\ServiceInterface\PaymentMethodVocabularyServiceInterface;

final readonly class PaymentMethodVocabularyService implements PaymentMethodVocabularyServiceInterface
{
    private const FALLBACK = [
        'internal' => [],
        'stripe' => [
            ['code' => 'card', 'label' => 'Card'],
        ],
        'paypal' => [
            ['code' => 'paypal', 'label' => 'PayPal'],
        ],
    ];

    public function __construct(private ?CatalogCategoryTypeVocabularyServiceInterface $catalogVocabulary = null)
    {
    }

    public function list(string $providerCode): array
    {
        $providerCode = strtolower(trim($providerCode));
        if ('' === $providerCode) {
            return [];
        }

        $types = $this->cataloguedTypes($providerCode);

        return [] === $types ? (self::FALLBACK[$providerCode] ?? []) : $types;
    }

    /** @return list<array{code: string, label: string}> */
    private function cataloguedTypes(string $providerCode): array
    {
        if (null === $this->catalogVocabulary) {
            return [];
        }

        try {
            $types = $this->catalogVocabulary->publishedTypes('payment', $providerCode);
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($types)) {
            return [];
        }

        $result = [];
        foreach ($types as $type) {
            if (!is_array($type)) {
                continue;
            }
            $code = strtolower(trim((string) ($type['code'] ?? '')));
            $label = trim((string) ($type['label'] ?? ''));
            if ('' !== $code && '' !== $label) {
                $result[] = ['code' => $code, 'label' => $label];
            }
        }

        return $result;
    }
}
