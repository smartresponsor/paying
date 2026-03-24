<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Functional\Ui;

use App\Entity\Payment\Payment;
use App\ValueObject\Payment\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleFixtureTableSmokeTest extends WebTestCase
{
    private ?string $originalOidcDisabled = null;

    protected function setUp(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        $_ENV['OIDC_DISABLED'] = '1';
        putenv('OIDC_DISABLED=1');
    }

    protected function tearDown(): void
    {
        if (null === $this->originalOidcDisabled) {
            unset($_ENV['OIDC_DISABLED']);
            putenv('OIDC_DISABLED');
        } else {
            $_ENV['OIDC_DISABLED'] = $this->originalOidcDisabled;
            putenv('OIDC_DISABLED='.$this->originalOidcDisabled);
        }

        parent::tearDown();
    }

    public function testConsoleListsRecentlyPersistedPayments(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        \assert($em instanceof EntityManagerInterface);

        $payment = new Payment(new Ulid(), PaymentStatus::processing, '25.00', 'USD');
        $payment->withProviderRef('fixture-console-row');
        $em->persist($payment);
        $em->flush();

        $client->request('GET', '/payment/console');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('fixture-console-row', (string) $client->getResponse()->getContent());
    }
}
