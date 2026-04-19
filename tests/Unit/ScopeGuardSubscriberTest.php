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

/**
 * Exercises the scope guard subscriber scenario within the payment unit test surface.
 */
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
     * Verifies that on controller logs verification failure and returns unauthorized.
     *
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
        $controller = [new ScopeGuardProtectedController(), 'secure'];
        $event = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onController($event);

        $guardedController = $event->getController();
        self::assertIsCallable($guardedController);

        $response = $guardedController();
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * Verifies that the subscriber ignores sub-requests.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \ReflectionException
     */

    /**
     * Verifies that any-of scope requirements accept either matching scope.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \ReflectionException
     */
    public function testOnControllerAcceptsAnyOfScopeRequirement(): void
    {
        $verifier = $this->createMock(TokenVerifierInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer good-token');

        $verifier->expects(self::once())
            ->method('verify')
            ->with('good-token')
            ->willReturn(['scope' => 'payment:read']);

        $verifier->expects(self::once())
            ->method('hasScopes')
            ->with(['scope' => 'payment:read'], ['payment:admin', 'payment:read'], true)
            ->willReturn(true);

        $logger->expects(self::never())->method('warning');

        $subscriber = new ScopeGuardSubscriber($verifier, $logger);
        $controller = [new ScopeGuardAnyOfProtectedController(), 'secure'];
        $event = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onController($event);

        self::assertSame($controller, $event->getController());
    }

    public function testOnControllerIgnoresSubRequest(): void
    {
        $verifier = $this->createMock(TokenVerifierInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $controller = [new ScopeGuardProtectedController(), 'secure'];
        $event = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::SUB_REQUEST);

        $verifier->expects(self::never())->method('verify');
        $logger->expects(self::never())->method('warning');

        $subscriber = new ScopeGuardSubscriber($verifier, $logger);
        $subscriber->onController($event);

        self::assertSame($controller, $event->getController());
    }
}

/**
 * Exercises the scope guard protected controller scenario within the payment unit test surface.
 */
final class ScopeGuardProtectedController
{
    #[RequireScope(['payment:read'])]
    /**
     * Provides the secure behavior required by this test scenario.
     */
    public function secure(): void
    {
    }
}

/**
 * Exercises any-of scope requirements within the payment unit test surface.
 */
final class ScopeGuardAnyOfProtectedController
{
    #[RequireScope(['payment:admin', 'payment:read'], any: true)]
    public function secure(): void
    {
    }
}
