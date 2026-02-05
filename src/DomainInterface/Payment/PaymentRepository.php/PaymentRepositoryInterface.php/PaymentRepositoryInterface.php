<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PaymentRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em);
    public function save(Payment $payment): void;
    public function find(string $id): ?Payment;
}
