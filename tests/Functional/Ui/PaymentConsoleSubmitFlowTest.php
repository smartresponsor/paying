<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Ui;

use App\Entity\Payment;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ValueObject\PaymentStatus;
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
        $client = self::createClient();
        $crawler = $client->request('GET', '/payment/console');

        $createForm = $crawler->selectButton('Create')->form([
            'payment_create[orderId]' => 'order-console-create-1001',
            'payment_create[amountMinor]' => '2599',
            'payment_create[currency]' => 'USD',
        ]);
        $client->submit($createForm);

        self::assertConsoleRedirectWithSelectedPayment();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('alert alert-success', $content);
        self::assertMatchesRegularExpression('/Payment [0-9A-HJKMNP-TV-Z]{26} created with status new\./', $content);

        $crawler = $client->request('GET', '/payment/console');
        $startForm = $crawler->selectButton('Start')->form([
            'payment_start[amount]' => '19.99',
            'payment_start[currency]' => 'USD',
            'payment_start[provider]' => 'internal',
        ]);
        $client->submit($startForm);

        self::assertConsoleRedirectWithSelectedPayment();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('alert alert-success', $content);
        self::assertMatchesRegularExpression('/Payment [0-9A-HJKMNP-TV-Z]{26} started via internal\./', $content);
    }

    public function testConsoleFinalizeAndRefundFormsMutateExistingFixtureBackedPayment(): void
    {
        $client = self::createClient();
        $repo = self::getContainer()->get(PaymentRepositoryInterface::class);
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

        self::assertConsoleRedirectWithSelectedPayment();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('alert alert-success', $content);
        self::assertStringContainsString(sprintf('Payment %s finalized with status completed.', (string) $payment->id()), $content);

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

        self::assertConsoleRedirectWithSelectedPayment();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('alert alert-success', $content);
        self::assertStringContainsString(sprintf('Payment %s refunded with status refunded.', (string) $payment->id()), $content);

        $refreshed = $repo->find((string) $payment->id());
        self::assertNotNull($refreshed);
        self::assertSame('refunded', $refreshed->status()->value);
    }

    public function testConsoleSelectionPrefillsFinalizeAndRefundPaymentIdFields(): void
    {
        $client = self::createClient();
        $repo = self::getContainer()->get(PaymentRepositoryInterface::class);
        \assert($repo instanceof PaymentRepositoryInterface);

        $payment = new Payment(new Ulid(), PaymentStatus::processing, '44.00', 'USD');
        $payment->withProviderRef('console-selected-prefill');
        $repo->save($payment);

        $crawler = $client->request('GET', '/payment/console?payment='.(string) $payment->id());

        self::assertResponseIsSuccessful();
        self::assertSame(
            (string) $payment->id(),
            (string) $crawler->filter('input[name="payment_console_finalize[paymentId]"]')->attr('value'),
        );
        self::assertSame(
            (string) $payment->id(),
            (string) $crawler->filter('input[name="payment_console_refund[paymentId]"]')->attr('value'),
        );
    }

    private static function assertConsoleRedirectWithSelectedPayment(): void
    {
        self::assertResponseStatusCodeSame(302);
        $location = self::getClient()->getResponse()->headers->get('Location');
        self::assertNotNull($location);
        self::assertMatchesRegularExpression('#^/payment/console\?payment=[0-9A-HJKMNP-TV-Z]{26}$#', $location);
    }
}
