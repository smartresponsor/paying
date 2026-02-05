<?php
namespace OrderComponent\Payment\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use OrderComponent\Payment\Api\Dto\PaymentCreateInput;
use OrderComponent\Payment\Api\Processor\PaymentCreateProcessor;

#[ApiResource(
    operations: [ new Post(input: PaymentCreateInput::class, processor: PaymentCreateProcessor::class, security: "is_granted('ROLE_ORDER_PAYMENT')") ],
    normalizationContext: ['groups' => ['payment:read']]
)]
class PaymentCreateRequest
{
    #[Groups(['payment:read'])]
    public string $status = 'accepted';
}