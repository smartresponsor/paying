<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\StartControllerInterface;
use App\Service\Payment\ProviderGuard;
use App\Service\Payment\IdempotencyService;
use App\Domain\Payment\PaymentStatus;
use App\Entity\Payment\Payment;
use App\InfrastructureInterface\Payment\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Payment\RequireScope;
use Symfony\Component\Uid\Ulid;

final class StartController implements StartControllerInterface
{
    #[RequireScope(['payment:write'])]

    public function __construct(
        private readonly ProviderGuard $guard,
        private readonly PaymentRepositoryInterface $repo,
        private readonly IdempotencyService $idem
    ) {}

    #[Route(path: '/payment/start', name: 'payment_start', methods: ['POST'])]
    public function start(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $amount = (string)($data['amount'] ?? '0.00');
        $currency = (string)($data['currency'] ?? 'USD');
        $provider = (string)($data['provider'] ?? 'internal');
        $key = (string)($request->headers->get('Idempotency-Key', ''));

        $payloadHash = hash('sha256', $request->getContent());
        $result = $this->idem->execute($key, $payloadHash, function () use ($amount, $currency, $provider) {
            $payment = new Payment(new Ulid(), PaymentStatus::new, $amount, $currency);
            $this->repo->save($payment);
            $resp = $this->guard->start($provider, $payment, []);
            return ['payment' => (string)$payment->id(), 'provider' => $provider, 'result' => $resp];
        });

        return new JsonResponse($result, 200);
    }
}
