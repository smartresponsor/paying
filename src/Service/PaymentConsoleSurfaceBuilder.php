<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Attribute\PaymentRequireScopeAttribute;
use App\Paying\Dto\Payment\PaymentConsoleFinalizeRequestDto;
use App\Paying\Dto\Payment\PaymentConsoleRefundRequestDto;
use App\Paying\Dto\Payment\PaymentCreateRequestDto;
use App\Paying\Dto\Payment\PaymentStartRequestDto;
use App\Paying\Form\PaymentConsoleFinalizeType;
use App\Paying\Form\PaymentConsoleRefundType;
use App\Paying\Form\PaymentCreateType;
use App\Paying\Form\PaymentStartType;
use App\Paying\Service\Payment\PaymentSurfaceContractFactory;
use App\Paying\ServiceInterface\PaymentConsoleCreateHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleFinalizeHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleReadModelInterface;
use App\Paying\ServiceInterface\PaymentConsoleRefundHandlerInterface;
use App\Paying\ServiceInterface\PaymentConsoleStartHandlerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Serves the operator console for creating, starting, finalizing, and refunding payments from a single back-office workflow.
 */
final readonly class PaymentConsoleSurfaceBuilder
{
    public function __construct(
        private PaymentSurfaceContractFactory $surfaceContractFactory,
        private PaymentConsoleCreateHandlerInterface $createHandler,
        private PaymentConsoleStartHandlerInterface $startHandler,
        private PaymentConsoleReadModelInterface $readModel,
        private PaymentConsoleFinalizeHandlerInterface $finalizeHandler,
        private PaymentConsoleRefundHandlerInterface $refundHandler,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    public function legacyConsole(): RedirectResponse
    {
        return new RedirectResponse($this->urlGenerator->generate('payment_console'), Response::HTTP_MOVED_PERMANENTLY);
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    public function refundLanding(): RedirectResponse
    {
        return new RedirectResponse($this->urlGenerator->generate('payment_console'), Response::HTTP_MOVED_PERMANENTLY);
    }

    #[PaymentRequireScopeAttribute(['payment:read'])]
    /**
     * Builds the console read model and binds all operator command forms for the currently selected payment context.
     */
    public function console(Request $request): array
    {
        $selectedPaymentId = trim((string) $request->query->get('payment', ''));
        $consoleView = $this->readModel->build(
            (string) $request->query->get('q', ''),
            (string) $request->query->get('status', 'all'),
            $selectedPaymentId,
        );

        [$finalizeDto, $refundDto] = $this->buildSelectedPaymentDtos($consoleView['selectedPayment']);

        $surface = $this->surfaceContractFactory->create(
            $consoleView,
            (string) $request->query->get('q', ''),
            (string) $request->query->get('status', 'all'),
            $selectedPaymentId,
        );

        return [
            '_view' => [
                'surface' => $surface->word,
                'operation' => $surface->view,
                'intent' => 'surface',
                'format' => 'auto',
                'component' => 'Paying',
            ],
            'locations' => $surface->slots,
            'data' => $surface->toTemplateContext() + [
                'create_form' => $this->buildCreateForm()->createView(),
                'start_form' => $this->buildStartForm()->createView(),
                'finalize_form' => $this->buildFinalizeForm($finalizeDto)->createView(),
                'refund_form' => $this->buildRefundForm($refundDto)->createView(),
            ],
            'meta' => [
                'source' => 'payment_console_controller',
            ],
        ];
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    /**
     * Handles back-office payment creation requests submitted from the operator console.
     */
    public function create(Request $request): RedirectResponse
    {
        $dto = new PaymentCreateRequestDto();
        $form = $this->formFactory->create(PaymentCreateType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Create payment form is invalid.');
        }

        $payment = $this->createHandler->create($dto->orderId, $dto->amountMinor, $dto->currency);
        $this->flash('success', sprintf('PaymentEntity %s created with status %s.', $payment->slug(), $payment->status()->value));

        return new RedirectResponse($this->urlGenerator->generate('payment_console', ['payment' => $payment->slug()]));
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    /**
     * Starts a provider-backed payment flow from the operator console.
     */
    public function start(Request $request): RedirectResponse
    {
        $dto = new PaymentStartRequestDto();
        $form = $this->formFactory->create(PaymentStartType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Start payment form is invalid.');
        }

        $payment = $this->startHandler->start($dto->orderId, $dto->provider, $dto->amount, $dto->currency);
        $this->flash('success', sprintf('PaymentEntity %s started via %s.', $payment->slug(), $dto->provider));

        return new RedirectResponse($this->urlGenerator->generate('payment_console', ['payment' => $payment->slug()]));
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    /**
     * Finalizes the selected payment from the operator console and redirects back to the refreshed read model.
     */
    public function finalize(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleFinalizeRequestDto();
        $form = $this->formFactory->create(PaymentConsoleFinalizeType::class, $dto);
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

        $this->flash('success', sprintf('PaymentEntity %s finalized with status %s.', $dto->paymentId, $payment->status()->value));

        return new RedirectResponse($this->urlGenerator->generate('payment_console', ['payment' => $dto->paymentId]));
    }

    #[PaymentRequireScopeAttribute(['payment:write'])]
    /**
     * Triggers a refund command for the selected payment from the operator console.
     */
    public function refund(Request $request): RedirectResponse
    {
        $dto = new PaymentConsoleRefundRequestDto();
        $form = $this->formFactory->create(PaymentConsoleRefundType::class, $dto);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->invalidFormRedirect('Refund payment form is invalid.');
        }

        $payment = $this->refundHandler->refund($dto->paymentId, $dto->amount, $dto->provider);
        if (null === $payment) {
            return $this->paymentNotFoundRedirect($dto->paymentId);
        }

        $this->flash('success', sprintf('PaymentEntity %s refunded with status %s.', $payment->slug(), $payment->status()->value));

        return new RedirectResponse($this->urlGenerator->generate('payment_console', ['payment' => $payment->slug()]));
    }

    /**
     * Creates the payment creation form bound to the canonical console endpoint.
     */
    private function buildCreateForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactory->create(PaymentCreateType::class, new PaymentCreateRequestDto(), [
            'action' => $this->urlGenerator->generate('payment_console_create'),
        ]);
    }

    /**
     * Creates the payment start form bound to the canonical console endpoint.
     */
    private function buildStartForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactory->create(PaymentStartType::class, new PaymentStartRequestDto(), [
            'action' => $this->urlGenerator->generate('payment_console_start'),
        ]);
    }

    /**
     * Creates the payment finalize form with any selected-payment defaults already prefilled.
     */
    private function buildFinalizeForm(PaymentConsoleFinalizeRequestDto $dto): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactory->create(PaymentConsoleFinalizeType::class, $dto, [
            'action' => $this->urlGenerator->generate('payment_console_finalize'),
        ]);
    }

    /**
     * Creates the payment refund form with any selected-payment defaults already prefilled.
     */
    private function buildRefundForm(PaymentConsoleRefundRequestDto $dto): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactory->create(PaymentConsoleRefundType::class, $dto, [
            'action' => $this->urlGenerator->generate('payment_console_refund'),
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
        $this->flash('danger', $message);

        return new RedirectResponse($this->urlGenerator->generate('payment_console'));
    }

    /**
     * Redirects back to the console when the selected payment can no longer be resolved by id.
     */
    private function paymentNotFoundRedirect(string $paymentId): RedirectResponse
    {
        $this->flash('danger', sprintf('PaymentEntity %s was not found.', $paymentId));

        return new RedirectResponse($this->urlGenerator->generate('payment_console'));
    }

    private function flash(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof \Symfony\Component\HttpFoundation\Session\Session) {
            $session->getFlashBag()->add($type, $message);
        }
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
