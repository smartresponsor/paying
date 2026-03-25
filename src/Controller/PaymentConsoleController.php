<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequireScope;
use App\Controller\Dto\PaymentConsoleFinalizeRequestDto;
use App\Controller\Dto\PaymentConsoleRefundRequestDto;
use App\Controller\Dto\PaymentCreateRequestDto;
use App\Controller\Dto\PaymentStartRequestDto;
use App\Form\PaymentConsoleFinalizeType;
use App\Form\PaymentConsoleRefundType;
use App\Form\PaymentCreateType;
use App\Form\PaymentStartType;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentService;
use App\ServiceInterface\PaymentConsoleReadModelInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PaymentStartServiceInterface $paymentStartService,
        private readonly ProviderGuardInterface $guard,
        private readonly RefundServiceInterface $refundService,
        private readonly PaymentRepositoryInterface $repo,
        private readonly PaymentConsoleReadModelInterface $readModel,
    ) {
    }

    #[RequireScope(['payment:read'])]
    public function console(Request $request): Response
    {
        $selectedPaymentId = trim((string) $request->query->get('payment', ''));
        $consoleView = $this->readModel->build(
            (string) $request->query->get('q', ''),
            (string) $request->query->get('status', 'all'),
            $selectedPaymentId,
        );

        $createForm = $this->createForm(PaymentCreateType::class, new PaymentCreateRequestDto(), [
            'action' => $this->generateUrl('payment_console_create', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
        $startForm = $this->createForm(PaymentStartType::class, new PaymentStartRequestDto(), [
            'action' => $this->generateUrl('payment_console_start', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);

        $finalizeDto = new PaymentConsoleFinalizeRequestDto();
        $refundDto = new PaymentConsoleRefundRequestDto();
        if (null !== $consoleView['selectedPayment']) {
            $finalizeDto->paymentId = (string) $consoleView['selectedPayment']['id'];
            $refundDto->paymentId = (string) $consoleView['selectedPayment']['id'];
        }

        $finalizeForm = $this->createForm(PaymentConsoleFinalizeType::class, $finalizeDto, [
            'action' => $this->generateUrl('payment_console_finalize', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
        $refundForm = $this->createForm(PaymentConsoleRefundType::class, $refundDto, [
            'action' => $this->generateUrl('payment_console_refund', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);

        return $this->render('payment/console.html.twig', [
            'create_form' => $createForm->createView(),
            'start_form' => $startForm->createView(),
            'finalize_form' => $finalizeForm->createView(),
            'refund_form' => $refundForm->createView(),
            'payments' => $consoleView['payments'],
            'selected_payment' => $consoleView['selectedPayment'],
            'webhook_events' => $consoleView['events'],
            'filters' => $consoleView['filters'],
        ]);
    }

    #[RequireScope(['payment:write'])]
    public function create(Request $request): RedirectResponse
    {
        $dto = new PaymentCreateRequestDto();
        $form = $this->createForm(PaymentCreateType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Create payment form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        $payment = $this->paymentService->create($dto->orderId, $dto->amountMinor, $dto->currency);
        $this->addFlash('success', sprintf('Payment %s created with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    #[RequireScope(['payment:write'])]
    public function start(Request $request): RedirectResponse
    {
        $dto = new PaymentStartRequestDto();
        $form = $this->createForm(PaymentStartType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Start payment form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        $started = $this->paymentStartService->start($dto->provider, $dto->amount, $dto->currency, '', 'payment-console');
        $payment = $started['payment'];

        $this->addFlash('success', sprintf('Payment %s started via %s.', (string) $payment->id(), $dto->provider));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    #[RequireScope(['payment:write'])]
    public function finalize(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleFinalizeRequestDto();
        $form = $this->createForm(PaymentConsoleFinalizeType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Finalize payment form is invalid.');

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

        $this->addFlash('success', sprintf('Payment %s finalized with status %s.', $dto->paymentId, $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => $dto->paymentId]);
    }

    #[RequireScope(['payment:write'])]
    public function refund(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleRefundRequestDto();
        $form = $this->createForm(PaymentConsoleRefundType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Refund payment form is invalid.');

            return $this->redirectToRoute('payment_console');
        }

        try {
            $payment = $this->refundService->refund(new Ulid($dto->paymentId), $dto->amount, $dto->provider);
        } catch (\RuntimeException $exception) {
            error_log(sprintf('[payment-console-refund] unable to refund payment %s: %s', $dto->paymentId, $exception->getMessage()));
            $this->addFlash('danger', sprintf('Payment %s was not found.', $dto->paymentId));

            return $this->redirectToRoute('payment_console');
        }

        $this->addFlash('success', sprintf('Payment %s refunded with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }
}
