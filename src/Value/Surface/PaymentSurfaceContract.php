<?php

declare(strict_types=1);

namespace App\Paying\Value\Surface;

use App\Paying\ValueObject\PaymentConsoleViewPayload;

/**
 * @deprecated Compatibility bridge retained only because this RC task forbids file deletion.
 *             Remove this class and the `Value/Surface` folder in the next destructive-authorized
 *             canonical cleanup after all callers use PaymentConsoleViewPayload.
 */
final readonly class PaymentSurfaceContract extends PaymentConsoleViewPayload
{
}
