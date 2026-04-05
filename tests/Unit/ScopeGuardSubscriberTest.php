<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Attribute\RequireScope;
use App\Infrastructure\ScopeGuardSubscriber;
use App\ServiceInterface\TokenVerifierInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ScopeGuardSubscriberTest extends TestCase
{
    private ?string $originalOidcDisabled = null;

    protected function setUp(): void
    {
        $this->originalOidcDisabled = $_ENV['OIDC_DISABLED'] ?? null;
        unset($_ENV['OIDC_DISABLED']);
        putenv('OIDC_DISABLED');
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

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \ReflectionException
     */
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

        $guardedController = $event->getController();
        self::assertIsCallable($guardedController);

        $response = $guardedController();
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(401, $response->getStatusCode());
    }
}

final class ScopeGuardProtectedController
{
    #[RequireScope(['payment:read'])]
    public function secure(): void
    {
    }
}
