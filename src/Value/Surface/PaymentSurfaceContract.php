<?php

declare(strict_types=1);

namespace App\Paying\Value\Surface;

use App\Interfacing\Contract\InterfaceSurfaceRenderableInterface;

final readonly class PaymentSurfaceContract implements InterfaceSurfaceRenderableInterface
{
    public const WORD = 'payment';
    public const VIEW_CONSOLE = 'console';

    /**
     * @param array<string, string> $slotMap
     * @param array<string, mixed>  $slots
     */
    public function __construct(
        public string $word,
        public string $view,
        public string $templateName,
        public array $slotMap,
        public string $query,
        public string $status,
        public string $selectedPaymentId,
        public array $payments,
        public ?array $selectedPayment,
        public array $webhookEvents,
        public array $filters,
        public array $slots,
    ) {
    }

    /**
     * @return array{word: string, view: string, templateName: string, slotMap: array<string, string>, query: string, status: string, selectedPaymentId: string, payments: array<int, array<string, mixed>>, selectedPayment: array<string, mixed>|null, webhookEvents: array<int, array<string, mixed>>, filters: array<string, mixed>, slots: array<string, mixed>}
     */
    public function toTemplateContext(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'templateName' => $this->templateName,
            'slotMap' => $this->slotMap,
            'query' => $this->query,
            'status' => $this->status,
            'selectedPaymentId' => $this->selectedPaymentId,
            'payments' => $this->payments,
            'selectedPayment' => $this->selectedPayment,
            'webhookEvents' => $this->webhookEvents,
            'filters' => $this->filters,
            'slots' => $this->slots,
        ];
    }

    /**
     * @return array{word: string, view: string, query: string, status: string, selectedPaymentId: string, payments: array<int, array<string, mixed>>, selectedPayment: array<string, mixed>|null, webhookEvents: array<int, array<string, mixed>>, filters: array<string, mixed>, slots: array<string, mixed>}
     */
    public function toFallbackData(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'query' => $this->query,
            'status' => $this->status,
            'selectedPaymentId' => $this->selectedPaymentId,
            'payments' => $this->payments,
            'selectedPayment' => $this->selectedPayment,
            'webhookEvents' => $this->webhookEvents,
            'filters' => $this->filters,
            'slots' => $this->slots,
        ];
    }

    public function templateName(): string
    {
        return $this->templateName;
    }
}
