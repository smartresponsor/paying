<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\ControllerInterface\WebhookControllerInterface;
use App\ServiceInterface\EventMapperInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\WebhookVerifierInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final class WebhookController implements WebhookControllerInterface
{
    public function __construct(
        private readonly WebhookVerifierInterface $verifier,
        private readonly ProviderGuardInterface $guard,
        /** @var iterable<EventMapperInterface> */ private readonly iterable $mappers,
    ) {
    }

    public function webhook(string $provider, Request $request): Response
    {
        try {
            $raw = $request->getContent();
            $headers = array_change_key_case($request->headers->all(), CASE_LOWER);
            $verified = $this->verifier->verify($provider, $raw, $headers);

            if (!$verified) {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($raw, true);
            if (!is_array($data)) {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            $paymentId = isset($data['payment']) ? (string) $data['payment'] : null;
            if (!$paymentId) {
                foreach ($this->mappers as $mapper) {
                    if ($mapper->provider() === strtolower($provider)) {
                        $paymentId = $mapper->extractPaymentId($data);
                        break;
                    }
                }
            }

            if (null !== $paymentId && Ulid::isValid($paymentId)) {
                $this->guard->reconcile($provider, new Ulid($paymentId));
            }

            return new Response('', Response::HTTP_OK);
        } catch (\Throwable) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
    }
}
