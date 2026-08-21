<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\PaymentMethodVocabularyService;
use PHPUnit\Framework\TestCase;

final class PaymentMethodVocabularyServiceTest extends TestCase
{
    public function testCatalogingVocabularyIsPrimary(): void
    {
        $catalog = new class {
            public function publishedTypes(string $catalogCode, string $categoryPath): array
            {
                return [['code' => 'wallet', 'label' => 'Wallet']];
            }
        };

        self::assertSame([['code' => 'wallet', 'label' => 'Wallet']], (new PaymentMethodVocabularyService($catalog))->list('stripe'));
    }

    public function testLocalMethodVocabularyIsFallback(): void
    {
        $service = new PaymentMethodVocabularyService();

        self::assertSame(['card'], array_column($service->list('stripe'), 'code'));
    }
}
