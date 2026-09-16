<?php

declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentGatewayEntity;
use App\Paying\RepositoryInterface\PaymentGatewayRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentGatewayEntity> */
final class PaymentGatewayRepository extends ServiceEntityRepository implements PaymentGatewayRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentGatewayEntity::class);
    }
}
