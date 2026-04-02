<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
use App\ServiceInterface\PaymentConsoleCreateHandlerInterface;
use App\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\ServiceInterface\PaymentConsoleReadModelInterface;
use App\ServiceInterface\PaymentConsoleRefundHandlerInterface;
use App\ServiceInterface\PaymentConsoleStartHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Operator-facing controller for the payment console.
 *
 * The console aggregates several workflows in one screen:
 * - create a payment aggregate
 * - start a provider flow
 * - finalize an existing payment
 * - refund an existing payment
 * - inspect recent payment and webhook state through the read model
 *
 * The controller itself stays transport-oriented and delegates all payment
 * mutations to dedicated handlers.
 */
final class PaymentConsoleController extends AbstractController
{
    public function __construct(
        private readonly PaymentConsoleCreateHandlerInterface $createHandler,
        private readonly PaymentConsoleStartHandlerInterface $startHandler,
        private readonly PaymentConsoleReadModelInterface $readModel,
        private readonly PaymentConsoleFinalizeHandlerInterface $finalizeHandler,
        private readonly PaymentConsoleRefundHandlerInterface $refundHandler,
    ) {
    }

    /**
     * Renders the console screen with current filters, recent payments, and
     * operator action forms.
     */
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
            'action' => $this->generateUrl('payment_console_create'),
        ]);

        $startDto = new PaymentStartRequestDto();
        $startDto->provider = 'internal';
        if (null !== $consoleView['selectedPayment']) {
            $selectedPayment = $consoleView['selectedPayment'];
            $startDto->orderId = (string) ($selectedPayment['orderId'] ?? '');
            $startDto->currency = (string) ($selectedPayment['currency'] ?? 'USD');
            $startDto->provider = $this->resolveProvider((string) ($selectedPayment['providerRef'] ?? ''));
        }

        $startForm = $this->createForm(PaymentStartType::class, $startDto, [
            'action' => $this->generateUrl('payment_console_start'),
        ]);

        $finalizeDto = new PaymentConsoleFinalizeRequestDto();
        $refundDto = new PaymentConsoleRefundRequestDto();
        if (null !== $consoleView['selectedPayment']) {
            $selectedPayment = $consoleView['selectedPayment'];
            $resolvedProvider = $this->resolveProvider((string) ($selectedPayment['providerRef'] ?? ''));

            $finalizeDto->paymentId = (string) $selectedPayment['id'];
            $finalizeDto->provider = $resolvedProvider;
            $finalizeDto->providerRef = (string) ($selectedPayment['providerRef'] ?? '');

            $refundDto->paymentId = (string) $selectedPayment['id'];
            $refundDto->provider = $resolvedProvider;
        }

        $finalizeForm = $this->createForm(PaymentConsoleFinalizeType::class, $finalizeDto, [
            'action' => $this->generateUrl('payment_console_finalize'),
        ]);
        $refundForm = $this->createForm(PaymentConsoleRefundType::class, $refundDto, [
            'action' => $this->generateUrl('payment_console_refund'),
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

    /**
     * Handles payment aggregate creation from the operator console.
     */
    #[RequireScope(['payment:write'])]
    public function create(Request $request): RedirectResponse
    {
        $dto = new PaymentCreateRequestDto();
        $form = $this->createForm(PaymentCreateType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Create payment form is invalid.');
        }

        $payment = $this->createHandler->create($dto->orderId, $dto->amountMinor, $dto->currency);
        $this->addFlash('success', sprintf('Payment %s created with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    /**
     * Handles provider start flow from the operator console.
     */
    #[RequireScope(['payment:write'])]
    public function start(Request $request): RedirectResponse
    {
        $dto = new PaymentStartRequestDto();
        $form = $this->createForm(PaymentStartType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Start payment form is invalid.');
        }

        $payment = $this->startHandler->start($dto->orderId, $dto->provider, $dto->amount, $dto->currency);

        $this->addFlash('success', sprintf('Payment %s started via %s.', (string) $payment->id(), $dto->provider));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    /**
     * Handles provider-side finalize flow from the console.
     */
    #[RequireScope(['payment:write'])]
    public function finalize(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleFinalizeRequestDto();
        $form = $this->createForm(PaymentConsoleFinalizeType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Finalize payment form is invalid.');
        }

        $payment = $this->finalizeHandler->finalize(
            $dto->paymentId,
            $dto->provider,
            $dto->providerRef,
            $dto->providerTransactionId,
            $dto->status,
        );
        if (null === $payment) {
            return $this->paymentNotFoundRedirect($dto->paymentId);
        }

        $this->addFlash('success', sprintf('Payment %s finalized with status %s.', $dto->paymentId, $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => $dto->paymentId]);
    }

    /**
     * Handles refund flow from the operator console.
     */
    #[RequireScope(['payment:write'])]
    public function refund(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleRefundRequestDto();
        $form = $this->createForm(PaymentConsoleRefundType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Refund payment form is invalid.');
        }

        $payment = $this->refundHandler->refund($dto->paymentId, $dto->amount, $dto->provider);
        if (null === $payment) {
            return $this->paymentNotFoundRedirect($dto->paymentId);
        }

        $this->addFlash('success', sprintf('Payment %s refunded with status %s.', (string) $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    /**
     * Redirects back to the console with an operator-visible validation error.
     */
    private function invalidFormRedirect(string $message): RedirectResponse
    {
        $this->addFlash('danger', $message);

        return $this->redirectToRoute('payment_console');
    }

    /**
     * Redirects back to the console when the requested payment does not exist.
     */
    private function paymentNotFoundRedirect(string $paymentId): RedirectResponse
    {
        $this->addFlash('danger', sprintf('Payment %s was not found.', $paymentId));

        return $this->redirectToRoute('payment_console');
    }

    /**
     * Resolves provider code from an already stored provider reference.
     *
     * This keeps console forms aligned with the persisted payment without asking
     * operators to infer the provider manually.
     */
    private function resolveProvider(string $providerRef): string
    {
        $normalized = strtolower(trim($providerRef));
        if ('' === $normalized) {
            return 'internal';
        }

        if (str_starts_with($normalized, 'stripe_') || str_starts_with($normalized, 'cs_')) {
            return 'stripe';
        }

        if (str_starts_with($normalized, 'paypal_')) {
            return 'paypal';
        }

        if (str_starts_with($normalized, 'internal')) {
            return 'internal';
        }

        return 'internal';
    }
}
