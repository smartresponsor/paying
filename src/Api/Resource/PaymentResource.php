<?php
namespace OrderComponent\Payment\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(security: "is_granted('ROLE_ORDER_PAYMENT')")
    ],
    normalizationContext: ['groups' => ['payment:read']]
)]
class PaymentResource
{
    #[Groups(['payment:read'])]
    public string $status = 'accepted';
}
