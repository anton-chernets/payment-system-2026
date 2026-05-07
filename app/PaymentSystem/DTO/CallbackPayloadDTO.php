<?php

namespace App\PaymentSystem\DTO;

use App\PaymentSystem\Enums\PaymentStatus;

final class CallbackPayloadDTO
{
    public function __construct(
        public readonly string        $transactionId,
        public readonly string        $orderId,
        public readonly PaymentStatus $status,
        public readonly float         $amount,
        public readonly string        $currency,
    ) {}
}
