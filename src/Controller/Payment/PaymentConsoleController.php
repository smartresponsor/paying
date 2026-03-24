<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Attribute\Payment\RequireScope;
use App\Controller\Payment\Dto\PaymentConsoleFinalizeRequestDto;
use App\Controller\Payment\Dto\PaymentConsoleRefundRequestDto;
use App\Controller\Payment\Dto\PaymentCreateRequestDto;
use App\Controller\Payment\Dto\PaymentStartRequestDto;
use App\Entity\Payment\Payment;
use App\Form\Payment\PaymentConsoleFinalizeType;
use App\Form\Payment\PaymentConsoleRefundType;
use App\Form\Payment\PaymentCreateType;
use App\Form\Payment\PaymentStartType;
use App\Repository\Payment\PaymentRepositoryInterface;
use App\ServiceInterface\Payment\PaymentServiceInterface;
use App\Service\Payment\ProviderGuardInterface;
use App\Service\Payment\RefundServiceInterface;
use App\ValueObject\Payment\PaymentStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleController extends AbstractController
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
        private readonly ProviderGuardInterface $guard,
        private readonly RefundServiceInterface $refundService,
        private readonly PaymentRepositoryInterface $repo,
    ) {
    }

    #[RequireScope(['payment:read'])]
    public function console(Request $request): Response
    {
        $createForm = $this->createForm(PaymentCreateType::class, new PaymentCreateRequestDto(), [
            'action' => $this->generateUrl('payment_console_create', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
        $startForm = $this->createForm(PaymentStartType::class, new PaymentStartRequestDto(), [
            'action' => $this->generateUrl('payment_console_start', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
        $finalizeForm = $this->createForm(PaymentConsoleFinalizeType::class, new PaymentConsoleFinalizeRequestDto(), [
            'action' => $this->generateUrl('payment_console_finalize', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
        $refundForm = $this->createForm(PaymentConsoleRefundType::class, new PaymentConsoleRefundRequestDto(), [
            'action' => $this->generateUrl('payment_console_refund', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);

        return $this->render('payment/console.html.twig', [
            'create_form' => $createForm->createView(),
            'start_form' => $startForm->createView(),
            'finalize_form' => $finalizeForm->createView(),
            'refund_form' => $refundForm->createView(),
            'recent_payments' => $this->repo->listRecent(12),
        ]);
    }

    #[RequireScope(['payment:write'])]
    public function create(Request $request): RedirectResponse
    {
        $dto = new PaymentCreateRequestDto();
        $form = $this->createForm(PaymentCreateType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Payment create form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        $payment = $this->paymentService->create($dto->orderId, $dto->amountMinor, $dto->currency);
        $this->addFlash('success', sprintf('Created payment %s with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console');
    }

    #[RequireScope(['payment:write'])]
    public function start(Request $request): RedirectResponse
    {
        $dto = new PaymentStartRequestDto();
        $form = $this->createForm(PaymentStartType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Payment start form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        $payment = new Payment(new Ulid(), PaymentStatus::new, $dto->amount, $dto->currency);
        $this->repo->save($payment);

        $providerResult = $this->guard->start($dto->provider, $payment, [
            'projectId' => (string) $payment->id(),
            'origin' => 'payment-console',
        ]);

        $providerRef = isset($providerResult['providerRef']) ? (string) $providerResult['providerRef'] : null;
        $payment->markProcessing($providerRef);
        $this->repo->save($payment);

        $this->addFlash('success', sprintf('Started payment %s via %s.', (string) $payment->id(), $dto->provider));

        return $this->redirectToRoute('payment_console');
    }

    #[RequireScope(['payment:write'])]
    public function finalize(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleFinalizeRequestDto();
        $form = $this->createForm(PaymentConsoleFinalizeType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Payment finalize form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        $payment = $this->repo->find($dto->paymentId);
        if (null === $payment) {
            $this->addFlash('danger', sprintf('Payment %s was not found.', $dto->paymentId));

            return $this->redirectToRoute('payment_console');
        }

        $payload = array_filter([
            'providerRef' => $dto->providerRef,
            'gatewayTransactionId' => $dto->gatewayTransactionId,
            'status' => $dto->status,
        ], static fn (mixed $value): bool => is_string($value) && '' !== $value);

        $resolved = $this->guard->finalize($dto->provider, new Ulid($dto->paymentId), $payload);
        $payment->syncFrom($resolved);
        $this->repo->save($payment);

        $this->addFlash('success', sprintf('Finalized payment %s with status %s.', $dto->paymentId, $payment->status()->value));

        return $this->redirectToRoute('payment_console');
    }

    #[RequireScope(['payment:write'])]
    public function refund(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleRefundRequestDto();
        $form = $this->createForm(PaymentConsoleRefundType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Payment refund form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        try {
            $payment = $this->refundService->refund(new Ulid($dto->paymentId), $dto->amount, $dto->provider);
        } catch (\RuntimeException $exception) {
            error_log(sprintf('[payment-console-refund] unable to refund payment %s: %s', $dto->paymentId, $exception->getMessage()));
            $this->addFlash('danger', sprintf('Payment %s was not found.', $dto->paymentId));

            return $this->redirectToRoute('payment_console');
        }

        $this->addFlash('success', sprintf('Refunded payment %s with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console');
    }
}
