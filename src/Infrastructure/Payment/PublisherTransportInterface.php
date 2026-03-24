<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Infrastructure\Payment;

interface PublisherTransportInterface
{
    public function publish(string $topic, array $payload): void;
}
