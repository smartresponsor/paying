<?php

declare(strict_types=1);

namespace App\Paying\Service;

use App\Cataloging\ServiceInterface\CatalogCategoryVocabularyServiceInterface;
use App\Paying\ServiceInterface\PaymentProviderRouterInterface;
use App\Paying\ServiceInterface\PaymentProviderVocabularyServiceInterface;

final readonly class PaymentProviderVocabularyService implements PaymentProviderVocabularyServiceInterface
{
    private const FALLBACK = [
        ['code' => 'internal', 'label' => 'Internal'],
        ['code' => 'stripe', 'label' => 'Stripe'],
        ['code' => 'paypal', 'label' => 'PayPal'],
    ];

    public function __construct(
        private PaymentProviderRouterInterface $router,
        private ?CatalogCategoryVocabularyServiceInterface $catalogVocabulary = null,
    ) {
    }

    public function list(): array
    {
        $types = $this->cataloguedTypes();
        if ([] === $types) {
            $types = self::FALLBACK;
        }

        $result = [];
        foreach ($types as $type) {
            $result[] = [
                'code' => $type['code'],
                'label' => $type['label'],
                'available' => $this->isAvailable($type['code']),
            ];
        }

        return $result;
    }

    /** @return list<array{code: string, label: string}> */
    private function cataloguedTypes(): array
    {
        if (null === $this->catalogVocabulary) {
            return [];
        }

        try {
            $types = $this->catalogVocabulary->publishedCategories('payment');
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

    private function isAvailable(string $code): bool
    {
        try {
            $this->router->for($code);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
