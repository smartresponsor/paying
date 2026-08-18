<?php

declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentTokenEntity;
use App\Paying\RepositoryInterface\PaymentTokenRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentTokenEntity> */
final class PaymentTokenRepository extends ServiceEntityRepository implements PaymentTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentTokenEntity::class);
    }
}
