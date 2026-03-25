<?php

declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Infrastructure\Payment\ScopeGuardSubscriber;
use App\Service\Payment\TokenVerifierInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ScopeGuardSubscriberTest extends TestCase
{
    public function testRequestWithoutBearerTokenReturnsUnauthorizedBeforeControllerInstantiation(): void
    {
        unset($_ENV['OIDC_DISABLED']);

        $verifier = $this->createMock(TokenVerifierInterface::class);
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request = Request::create('/api/payments', 'POST');
        $request->attributes->set('_controller', 'App\\Controller\\Payment\\PaymentCreateController::create');

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new ScopeGuardSubscriber($verifier);
        $subscriber->onRequest($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(401, $event->getResponse()?->getStatusCode());
    }
}
