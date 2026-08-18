<?php

declare(strict_types=1);

namespace App\Paying\Repository;

use App\Paying\Entity\PaymentTranslationEntity;
use App\Paying\RepositoryInterface\PaymentTranslationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentTranslationEntity> */
final class PaymentTranslationRepository extends ServiceEntityRepository implements PaymentTranslationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentTranslationEntity::class);
    }
}
