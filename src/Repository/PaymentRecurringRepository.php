<?php

declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentRecurringEntity;
use App\Paying\RepositoryInterface\PaymentRecurringRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentRecurringEntity> */
final class PaymentRecurringRepository extends ServiceEntityRepository implements PaymentRecurringRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentRecurringEntity::class);
    }
}
