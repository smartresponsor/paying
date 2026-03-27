<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\Attribute\RequireScope;
use App\ServiceInterface\TokenVerifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ScopeGuardSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenVerifierInterface $verifier,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

    /**
     * @throws \ReflectionException
     */
    /**
     * @throws \ReflectionException
     */
    public function onController(ControllerEvent $event): void
    {
        if ('1' === (string) ($_ENV['OIDC_DISABLED'] ?? '')) {
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
        foreach ($ref->getAttributes(RequireScope::class) as $a) {
            /** @var RequireScope $attr */
            $attr = $a->newInstance();
            $reqs[] = $attr;
        }
        foreach ($classRef->getAttributes(RequireScope::class) as $a) {
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
