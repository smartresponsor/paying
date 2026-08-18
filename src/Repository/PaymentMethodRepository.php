<?php

declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentMethodEntity;
use App\Paying\RepositoryInterface\PaymentMethodRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentMethodEntity> */
final class PaymentMethodRepository extends ServiceEntityRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethodEntity::class);
    }
}
