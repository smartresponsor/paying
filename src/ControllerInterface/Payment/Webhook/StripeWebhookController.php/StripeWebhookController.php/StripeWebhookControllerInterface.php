<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ControllerInterface\Payment;

interface StripeWebhookControllerInterface
{
    public function __construct(private EntityManagerInterface $em, private StripeSignatureValidator $validator, private StripeEventNormalizer $normalizer, private JsonSchemaValidator $schema, private LoggerInterface $paymentAuditLogger);
    public function __invoke(Request $request): JsonResponse;
}
