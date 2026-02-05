<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ControllerInterface\Payment;

interface PayPalWebhookControllerInterface
{
    public function __construct(private EntityManagerInterface $em, private PayPalSignatureValidator $validator, private PayPalEventNormalizer $normalizer, private JsonSchemaValidator $schema, private LoggerInterface $paymentAuditLogger);
    public function __invoke(Request $request): JsonResponse;
}
