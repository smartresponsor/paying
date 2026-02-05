<?php
namespace OrderComponent\Payment\Api\Resource;

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