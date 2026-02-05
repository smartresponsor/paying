<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Domain\Payment;

use App\DomainInterface\Payment\TransitionHandlerInterface;
use App\Entity\Payment\Payment;
use Symfony\Component\Workflow\WorkflowInterface;

class TransitionHandler implements TransitionHandlerInterface
{
    public function __construct(private readonly WorkflowInterface $workflow) {}

    public function apply(Payment $payment, string $transition): bool
    {
        if (!$this->workflow->can($payment, $transition)) {
            return false;
        }
        $this->workflow->apply($payment, $transition);
        return true;
    }
}
