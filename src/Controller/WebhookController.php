<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\ControllerInterface\WebhookControllerInterface;
use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use App\ServiceInterface\EventMapperInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\WebhookVerifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

final readonly class WebhookController implements WebhookControllerInterface
{
    public function __construct(
        private WebhookVerifierInterface $verifier,
        private ProviderGuardInterface $guard,
        private ApiJsonBodyDecoderInterface $jsonBodyDecoder,
        /** @var iterable<EventMapperInterface> */ private iterable $mappers,
        private LoggerInterface $logger,
    ) {
    }

    public function webhook(string $provider, Request $request): Response
    {
        try {
            $raw = $request->getContent();
            $headers = array_change_key_case($request->headers->all());
            $verified = $this->verifier->verify($provider, $raw, $headers);

            if (!$verified) {
                $this->logger->warning('Webhook verification failed.', [
                    'provider' => $provider,
                ]);

                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            $data = $this->jsonBodyDecoder->decode($request);
            if (!is_array($data)) {
                $this->logger->warning('Webhook payload could not be decoded to array.', [
                    'provider' => $provider,
                ]);

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
            } elseif (null === $paymentId) {
                $this->logger->warning('Webhook payload did not resolve a payment identifier.', [
                    'provider' => $provider,
                ]);
            }

            return new Response('', Response::HTTP_OK);
        } catch (\Throwable $exception) {
            $this->logger->error('Webhook processing failed.', [
                'provider' => $provider,
                'exception' => $exception,
            ]);

            return new Response('', Response::HTTP_BAD_REQUEST);
        }
    }
}
