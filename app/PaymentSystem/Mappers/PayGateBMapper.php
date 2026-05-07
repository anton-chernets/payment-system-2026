<?php

namespace App\PaymentSystem\Mappers;

use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Enums\PaymentStatus;

/**
 * PayGateB wire format:
 *   create request:  { order, total (cents), currency_code, note }
 *   create response: { id, redirect_url, state }
 *   callback:        { id, order, state: created|success|error }
 */
class PayGateBMapper
{
    private array $statusMap = [
        'created' => PaymentStatus::Pending,
        'success' => PaymentStatus::Success,
        'error'   => PaymentStatus::Failed,
    ];

    public function toProviderRequest(CreatePaymentDTO $dto): array
    {
        return [
            'order'         => $dto->orderId,
            'total'         => (int) round($dto->amount * 100),
            'currency_code' => $dto->currency,
            'note'          => $dto->description,
        ];
    }

    public function fromProviderResponse(array $response): PaymentResponseDTO
    {
        return new PaymentResponseDTO(
            transactionId: $response['id'],
            redirectUrl:   $response['redirect_url'],
            status:        PaymentStatus::Pending,
        );
    }

    public function fromCallback(array $payload): CallbackPayloadDTO
    {
        return new CallbackPayloadDTO(
            transactionId: $payload['id'],
            orderId:       $payload['order'],
            status:        $this->statusMap[$payload['state']] ?? PaymentStatus::Failed,
            amount:        0,
            currency:      '',
        );
    }
}
