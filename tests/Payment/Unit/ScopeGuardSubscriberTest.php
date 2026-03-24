<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Attribute\Payment\RequireScope;
use App\Infrastructure\Payment\ScopeGuardSubscriber;
use App\Service\Payment\TokenVerifierInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ScopeGuardSubscriberTest extends TestCase
{
    public function testOnControllerLogsVerificationFailureAndReturnsUnauthorized(): void
    {
        $verifier = $this->createMock(TokenVerifierInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer bad-token');

        $verifier->expects(self::once())
            ->method('verify')
            ->with('bad-token')
            ->willThrowException(new \RuntimeException('bad token'));

        $logger->expects(self::once())
            ->method('warning')
            ->with(
                'Scope guard rejected bearer token due to verification failure.',
                self::callback(static function (array $context): bool {
                    return isset($context['exception']) && $context['exception'] instanceof \RuntimeException;
                }),
            );

        $subscriber = new ScopeGuardSubscriber($verifier, $logger);
        $event = new ControllerEvent($kernel, [new ScopeGuardProtectedController(), 'secure'], $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onController($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(401, $event->getResponse()?->getStatusCode());
    }
}

final class ScopeGuardProtectedController
{
    #[RequireScope(['payment:read'])]
    public function secure(): void
    {
    }
}
