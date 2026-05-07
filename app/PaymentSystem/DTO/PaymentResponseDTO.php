<?php

namespace App\PaymentSystem\DTO;

use App\PaymentSystem\Enums\PaymentStatus;

final class PaymentResponseDTO
{
    public function __construct(
        public readonly string        $transactionId,
        public readonly string        $redirectUrl,
        public readonly PaymentStatus $status,
    ) {}
}
