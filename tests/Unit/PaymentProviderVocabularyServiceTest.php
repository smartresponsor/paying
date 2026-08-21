<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\PaymentProviderVocabularyService;
use App\Paying\ServiceInterface\PaymentProviderInterface;
use App\Paying\ServiceInterface\PaymentProviderRouterInterface;
use PHPUnit\Framework\TestCase;

final class PaymentProviderVocabularyServiceTest extends TestCase
{
    public function testCatalogingIsPrimaryWhileRouterDefinesAvailability(): void
    {
        $router = $this->createMock(PaymentProviderRouterInterface::class);
        $provider = $this->createStub(PaymentProviderInterface::class);
        $router->method('for')->willReturnCallback(static function (string $code) use ($provider): PaymentProviderInterface {
            if ('stripe' === $code) {
                return $provider;
            }

            throw new \InvalidArgumentException('Unknown provider');
        });

        $catalog = new class {
            public function publishedCategories(string $catalogCode): array
            {
                return [
                    ['code' => 'stripe', 'label' => 'Stripe'],
                    ['code' => 'adyen', 'label' => 'Adyen'],
                ];
            }
        };

        self::assertSame([
            ['code' => 'stripe', 'label' => 'Stripe', 'available' => true],
            ['code' => 'adyen', 'label' => 'Adyen', 'available' => false],
        ], (new PaymentProviderVocabularyService($router, $catalog))->list());
    }

    public function testInternalVocabularyIsFallbackWhenCatalogingFails(): void
    {
        $router = $this->createMock(PaymentProviderRouterInterface::class);
        $provider = $this->createStub(PaymentProviderInterface::class);
        $router->method('for')->willReturn($provider);
        $catalog = new class {
            public function publishedCategories(string $catalogCode): array
            {
                throw new \RuntimeException('Cataloging unavailable');
            }
        };

        self::assertSame(
            ['internal', 'stripe', 'paypal'],
            array_column((new PaymentProviderVocabularyService($router, $catalog))->list(), 'code'),
        );
    }
}
