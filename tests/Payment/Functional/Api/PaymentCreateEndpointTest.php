<?php
namespace OrderComponent\Payment\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentCreateEndpointTest extends WebTestCase
{
    public function testCreatePaymentAccepted(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/payment_create_requests', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            "orderId" => "00000000-0000-0000-0000-000000000001",
            "amountMinor" => 5000,
            "currency" => "USD",
            "gatewayCode" => "stripe"
        ]));
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}