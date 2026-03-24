<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

interface DlqServiceInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array;

    public function replay(int $id): bool;
}
