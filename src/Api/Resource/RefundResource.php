<?php
namespace OrderComponent\Payment\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(security: "is_granted('ROLE_ORDER_REFUND')")
    ],
    normalizationContext: ['groups' => ['refund:read']]
)]
class RefundResource
{
    #[Groups(['refund:read'])]
    public string $status = 'accepted';
}
