<?php
namespace App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\Service\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use OrderComponent\Payment\Api\Dto\PaymentRefundInput;
use OrderComponent\Payment\Api\Processor\PaymentRefundProcessor;

#[ApiResource(
    operations: [ new Post(input: PaymentRefundInput::class, processor: PaymentRefundProcessor::class, security: "is_granted('ROLE_ORDER_REFUND')") ],
    normalizationContext: ['groups' => ['refund:read']]
)]
class PaymentRefundRequest
{
    #[Groups(['refund:read'])]
    public string $status = 'accepted';
}