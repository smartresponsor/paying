<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Functional\Ui;

use App\Entity\Payment\Payment;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\ValueObject\Payment\PaymentStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleSubmitFlowTest extends WebTestCase
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

    public function testConsoleCreateAndStartFormsRedirectWithSuccessFlash(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/payment/console');

        $createForm = $crawler->selectButton('Create')->form([
            'payment_create[orderId]' => 'order-console-create-1001',
            'payment_create[amountMinor]' => '2599',
            'payment_create[currency]' => 'USD',
        ]);
        $client->submit($createForm);

        self::assertResponseRedirects('/payment/console');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Created payment', (string) $client->getResponse()->getContent());

        $crawler = $client->request('GET', '/payment/console');
        $startForm = $crawler->selectButton('Start')->form([
            'payment_start[amount]' => '19.99',
            'payment_start[currency]' => 'USD',
            'payment_start[provider]' => 'internal',
        ]);
        $client->submit($startForm);

        self::assertResponseRedirects('/payment/console');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Started payment', (string) $client->getResponse()->getContent());
    }

    public function testConsoleFinalizeAndRefundFormsMutateExistingFixtureBackedPayment(): void
    {
        $client = static::createClient();
        $repo = static::getContainer()->get(PaymentRepositoryInterface::class);
        \assert($repo instanceof PaymentRepositoryInterface);

        $payment = new Payment(new Ulid(), PaymentStatus::processing, '33.00', 'USD');
        $payment->withProviderRef('console-seeded-provider-ref');
        $repo->save($payment);

        $crawler = $client->request('GET', '/payment/console');
        $finalizeForm = $crawler->selectButton('Finalize')->form([
            'payment_console_finalize[paymentId]' => (string) $payment->id(),
            'payment_console_finalize[provider]' => 'internal',
            'payment_console_finalize[providerRef]' => 'console-finalized-ref',
            'payment_console_finalize[gatewayTransactionId]' => 'txn-console-finalize-1',
            'payment_console_finalize[status]' => 'completed',
        ]);
        $client->submit($finalizeForm);

        self::assertResponseRedirects('/payment/console');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Finalized payment', (string) $client->getResponse()->getContent());

        $refreshed = $repo->find((string) $payment->id());
        self::assertNotNull($refreshed);
        self::assertSame('completed', $refreshed->status()->value);

        $crawler = $client->request('GET', '/payment/console');
        $refundForm = $crawler->selectButton('Refund')->form([
            'payment_console_refund[paymentId]' => (string) $payment->id(),
            'payment_console_refund[amount]' => '10.00',
            'payment_console_refund[provider]' => 'internal',
        ]);
        $client->submit($refundForm);

        self::assertResponseRedirects('/payment/console');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Refunded payment', (string) $client->getResponse()->getContent());

        $refreshed = $repo->find((string) $payment->id());
        self::assertNotNull($refreshed);
        self::assertSame('refunded', $refreshed->status()->value);
    }
}
