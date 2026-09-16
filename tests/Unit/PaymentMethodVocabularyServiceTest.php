<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Cataloging\ServiceInterface\CatalogCategoryTypeVocabularyServiceInterface;
use App\Paying\Service\PaymentMethodVocabularyService;
use PHPUnit\Framework\TestCase;

final class PaymentMethodVocabularyServiceTest extends TestCase
{
    public function testCatalogingVocabularyIsPrimary(): void
    {
        $catalog = new class implements CatalogCategoryTypeVocabularyServiceInterface {
            public function publishedTypes(string $catalogCode, string $categoryPath, string $tenant = 'default'): array
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
