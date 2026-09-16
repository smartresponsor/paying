<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Subscriber;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\ServiceInterface\PaymentTokenVerifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Guards payment HTTP requests by checking required application scopes.
 */
readonly class PaymentScopeGuardSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PaymentTokenVerifierInterface $verifier,
        private LoggerInterface $logger,
        private bool $oidcDisabled = false,
    ) {
    }

    /**
     * Returns the Symfony event subscriptions exposed by this subscriber.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

    /**
     * Validates controller access before the payment action executes.
     *
     * @throws \ReflectionException
     */
    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->oidcDisabled) {
            return;
        }

        $ctrl = $event->getController();
        if (!is_array($ctrl)) {
            return;
        }

        [$obj, $method] = $ctrl;
        $ref = new \ReflectionMethod($obj, $method);
        $classRef = new \ReflectionClass($obj);

        $reqs = [];
        foreach ($ref->getAttributes(PaymentRequireScopeAttribute::class) as $a) {
            /** @var PaymentRequireScopeAttribute $attr */
            $attr = $a->newInstance();
            $reqs[] = $attr;
        }
        foreach ($classRef->getAttributes(PaymentRequireScopeAttribute::class) as $a) {
            $attr = $a->newInstance();
            $reqs[] = $attr;
        }
        if (!$reqs) {
            return;
        }

        $request = $event->getRequest();
        $auth = (string) $request->headers->get('Authorization', '');
        if (!str_starts_with($auth, 'Bearer ')) {
            $event->setController(static fn (): JsonResponse => new JsonResponse(['error' => 'unauthorized'], 401));

            return;
        }
        $token = substr($auth, 7);
        try {
            $claims = $this->verifier->verify($token);
            foreach ($reqs as $r) {
                if (!$this->verifier->hasScopes($claims, $r->scopes, $r->any)) {
                    $event->setController(static fn (): JsonResponse => new JsonResponse(['error' => 'forbidden'], 403));

                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Scope guard rejected bearer token due to verification failure.', ['exception' => $e]);
            $event->setController(static fn (): JsonResponse => new JsonResponse(['error' => 'unauthorized'], 401));
        }
    }
}
