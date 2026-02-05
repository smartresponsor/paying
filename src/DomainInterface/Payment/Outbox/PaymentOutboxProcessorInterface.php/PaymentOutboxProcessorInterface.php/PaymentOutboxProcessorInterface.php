<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PaymentOutboxProcessorInterface
{
    public function __construct(private EntityManagerInterface $em, private TransportInterface $transport, // @messenger.transport.payment_outbox private LoggerInterface $logger);
    public function process(int $limit = 50, bool $retryFailed = false);
}
