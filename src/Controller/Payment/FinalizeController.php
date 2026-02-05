<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\FinalizeControllerInterface;
use App\Service\Payment\ProviderGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Payment\RequireScope;
use Symfony\Component\Uid\Ulid;

final class FinalizeController implements FinalizeControllerInterface
{
    #[RequireScope(['payment:write'])]

    public function __construct(private readonly ProviderGuard $guard) {}

    #[Route(path: '/payment/finalize/{id}', name: 'payment_finalize', methods: ['POST'])]
    public function finalize(string $id, Request $request): JsonResponse
    {
        $provider = (string)($request->query->get('provider', 'internal'));
        $payload = json_decode($request->getContent(), true) ?? [];
        $payment = $this->guard->finalize($provider, new Ulid($id), $payload);
        return new JsonResponse(['id' => (string)$payment->id(), 'status' => $payment->status()->value], 200);
    }
}
