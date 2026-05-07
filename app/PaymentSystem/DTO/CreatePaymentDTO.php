<?php

namespace App\PaymentSystem\DTO;

final class CreatePaymentDTO
{
    public function __construct(
        public readonly string $orderId,
        public readonly float  $amount,
        public readonly string $currency,
        public readonly string $description = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId:     $data['order_id'],
            amount:      (float) $data['amount'],
            currency:    strtoupper($data['currency']),
            description: $data['description'] ?? '',
        );
    }
}
