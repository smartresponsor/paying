<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\WebhookControllerInterface;
use App\Service\Payment\WebhookVerifier;
use App\ServiceInterface\Payment\EventMapperInterface;
use App\Service\Payment\ProviderGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

final class WebhookController implements WebhookControllerInterface
{
    public function __construct(
        private readonly WebhookVerifier $verifier,
        private readonly ProviderGuard $guard,
        /** @var iterable<EventMapperInterface> */ private readonly iterable $mappers
    ) {}

    #[Route(path: '/payment/webhook/{provider}', name: 'payment_webhook', methods: ['POST'])]
    public function webhook(string $provider, Request $request): Response
    {
        $raw = $request->getContent();
        if (!$this->verifier->verify($provider, $raw, $request->headers->all())) {
            return new Response('', 400);
        }
        // Optionally parse event to locate payment id
                $data = json_decode($raw, true) ?? [];
        $id = isset($data['payment']) ? (string)$data['payment'] : null;
        if (!$id) {
            foreach ($this->mappers as $mapper) {
                if ($mapper->provider() === strtolower($provider)) {
                    $id = $mapper->extractPaymentId($data);
                    break;
                }
            }
        }
        if ($id) {
            $this->guard->reconcile($provider, new Ulid($id));
        }
        return new Response('', 200);
    }
}
