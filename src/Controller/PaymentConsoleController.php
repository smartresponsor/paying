<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Controller;

use App\Paying\Attribute\RequireScope;
use App\Paying\Controller\Dto\PaymentConsoleFinalizeRequestDto;
use App\Paying\Controller\Dto\PaymentConsoleRefundRequestDto;
use App\Paying\Controller\Dto\PaymentCreateRequestDto;
use App\Paying\Controller\Dto\PaymentStartRequestDto;
use App\Paying\Form\PaymentConsoleFinalizeType;
use App\Paying\Form\PaymentConsoleRefundType;
use App\Paying\Form\PaymentCreateType;
use App\Paying\Form\PaymentStartType;
use App\Paying\ServiceInterface\PaymentConsoleCreateHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleReadModelInterface;
use App\Paying\ServiceInterface\PaymentConsoleRefundHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleStartHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the operator console for creating, starting, finalizing, and refunding payments from a single back-office workflow.
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

    #[RequireScope(['payment:read'])]
    /**
     * Builds the console read model and binds all operator command forms for the currently selected payment context.
     */
    public function console(Request $request): Response
    {
        $selectedPaymentId = trim((string) $request->query->get('payment', ''));
        $consoleView = $this->readModel->build(
            (string) $request->query->get('q', ''),
            (string) $request->query->get('status', 'all'),
            $selectedPaymentId,
        );

        [$finalizeDto, $refundDto] = $this->buildSelectedPaymentDtos($consoleView['selectedPayment']);

        return $this->render('payment/console.html.twig', [
            'create_form' => $this->buildCreateForm()->createView(),
            'start_form' => $this->buildStartForm()->createView(),
            'finalize_form' => $this->buildFinalizeForm($finalizeDto)->createView(),
            'refund_form' => $this->buildRefundForm($refundDto)->createView(),
            'payments' => $consoleView['payments'],
            'selected_payment' => $consoleView['selectedPayment'],
            'webhook_events' => $consoleView['events'],
            'filters' => $consoleView['filters'],
        ]);
    }

    #[RequireScope(['payment:write'])]
    /**
     * Handles back-office payment creation requests submitted from the operator console.
     */
    public function create(Request $request): RedirectResponse
    {
        $dto = new PaymentCreateRequestDto();
        $form = $this->createForm(PaymentCreateType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Create payment form is invalid.');
        }

        $payment = $this->createHandler->create($dto->orderId, $dto->amountMinor, $dto->currency);
        $this->addFlash('success', sprintf('Payment %s created with status %s.', $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    #[RequireScope(['payment:write'])]
    /**
     * Starts a provider-backed payment flow from the operator console.
     */
    public function start(Request $request): RedirectResponse
    {
        $dto = new PaymentStartRequestDto();
        $form = $this->createForm(PaymentStartType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Start payment form is invalid.');
        }

        $payment = $this->startHandler->start($dto->orderId, $dto->provider, $dto->amount, $dto->currency);

        $this->addFlash('success', sprintf('Payment %s started via %s.', $payment->id(), $dto->provider));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    #[RequireScope(['payment:write'])]
    /**
     * Finalizes the selected payment from the operator console and redirects back to the refreshed read model.
     */
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

    #[RequireScope(['payment:write'])]
    /**
     * Triggers a refund command for the selected payment from the operator console.
     */
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

        $this->addFlash('success', sprintf('Payment %s refunded with status %s.', $payment->id(), $payment->status()->value));

        return $this->redirectToRoute('payment_console', ['payment' => (string) $payment->id()]);
    }

    /**
     * Creates the payment creation form bound to the canonical console endpoint.
     */
    private function buildCreateForm(): FormInterface
    {
        return $this->createForm(PaymentCreateType::class, new PaymentCreateRequestDto(), [
            'action' => $this->generateUrl('payment_console_create'),
        ]);
    }

    /**
     * Creates the payment start form bound to the canonical console endpoint.
     */
    private function buildStartForm(): FormInterface
    {
        return $this->createForm(PaymentStartType::class, new PaymentStartRequestDto(), [
            'action' => $this->generateUrl('payment_console_start'),
        ]);
    }

    /**
     * Creates the payment finalize form with any selected-payment defaults already prefilled.
     */
    private function buildFinalizeForm(PaymentConsoleFinalizeRequestDto $dto): FormInterface
    {
        return $this->createForm(PaymentConsoleFinalizeType::class, $dto, [
            'action' => $this->generateUrl('payment_console_finalize'),
        ]);
    }

    /**
     * Creates the payment refund form with any selected-payment defaults already prefilled.
     */
    private function buildRefundForm(PaymentConsoleRefundRequestDto $dto): FormInterface
    {
        return $this->createForm(PaymentConsoleRefundType::class, $dto, [
            'action' => $this->generateUrl('payment_console_refund'),
        ]);
    }

    /**
     * Converts the selected payment row from the console read model into command DTO defaults for finalize and refund actions.
     *
     * @param array<string, mixed>|null $selectedPayment
     *
     * @return array{PaymentConsoleFinalizeRequestDto, PaymentConsoleRefundRequestDto}
     */
    private function buildSelectedPaymentDtos(?array $selectedPayment): array
    {
        $finalizeDto = new PaymentConsoleFinalizeRequestDto();
        $refundDto = new PaymentConsoleRefundRequestDto();

        if (null === $selectedPayment) {
            return [$finalizeDto, $refundDto];
        }

        $resolvedProvider = $this->resolveProvider((string) ($selectedPayment['providerRef'] ?? ''));

        $finalizeDto->paymentId = (string) $selectedPayment['id'];
        $finalizeDto->provider = $resolvedProvider;
        $finalizeDto->providerRef = (string) ($selectedPayment['providerRef'] ?? '');

        $refundDto->paymentId = (string) $selectedPayment['id'];
        $refundDto->provider = $resolvedProvider;

        return [$finalizeDto, $refundDto];
    }

    /**
     * Redirects back to the console after a form-level failure that never reached the application handlers.
     */
    private function invalidFormRedirect(string $message): RedirectResponse
    {
        $this->addFlash('danger', $message);

        return $this->redirectToRoute('payment_console');
    }

    /**
     * Redirects back to the console when the selected payment can no longer be resolved by id.
     */
    private function paymentNotFoundRedirect(string $paymentId): RedirectResponse
    {
        $this->addFlash('danger', sprintf('Payment %s was not found.', $paymentId));

        return $this->redirectToRoute('payment_console');
    }

    /**
     * Infers the provider surface from the persisted provider reference so the console can prefill a matching command form.
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

        return 'internal';
    }
}
