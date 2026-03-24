<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Attribute\Payment\RequireScope;
use App\Service\Payment\TokenVerifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ScopeGuardSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly TokenVerifierInterface $verifier)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

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
            $event->setResponse(new JsonResponse(['error' => 'unauthorized'], 401));

            return;
        }
        $token = substr($auth, 7);
        try {
            $claims = $this->verifier->verify($token);
            foreach ($reqs as $r) {
                if (!$this->verifier->hasScopes($claims, $r->scopes, $r->any)) {
                    $event->setResponse(new JsonResponse(['error' => 'forbidden'], 403));

                    return;
                }
            }
        } catch (\Throwable $e) {
            $event->setResponse(new JsonResponse(['error' => 'unauthorized'], 401));
        }
    }
}
