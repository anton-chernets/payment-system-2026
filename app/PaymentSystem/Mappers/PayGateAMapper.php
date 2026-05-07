<?php

namespace App\PaymentSystem\Mappers;

use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Enums\PaymentStatus;

/**
 * PayGateA wire format:
 *   create request:  { amount (string), currency, merchant_order_id }
 *   create response: { payment_id, payment_url, status }
 *   callback:        { payment_id, merchant_order_id, status: new|paid|rejected }
 */
class PayGateAMapper
{
    private array $statusMap = [
        'new'      => PaymentStatus::Pending,
        'paid'     => PaymentStatus::Success,
        'rejected' => PaymentStatus::Failed,
    ];

    private array $stateMap = [
        'created' => PaymentStatus::Pending,
        'success' => PaymentStatus::Success,
        'error'   => PaymentStatus::Failed,
    ];

    public function toProviderRequest(CreatePaymentDTO $dto): array
    {
        return [
            'merchant_order_id' => $dto->orderId,
            'amount'            => number_format($dto->amount, 2, '.', ''),
            'currency'          => $dto->currency,
        ];
    }

    public function fromProviderResponse(array $response): PaymentResponseDTO
    {
        return new PaymentResponseDTO(
            transactionId: $response['payment_id'],
            redirectUrl:   $response['payment_url'],
            status:        PaymentStatus::Pending,
        );
    }

    public function fromCallback(array $payload): CallbackPayloadDTO
    {
        if (isset($payload['payment_id'])) {
            return new CallbackPayloadDTO(
                transactionId: $payload['payment_id'],
                orderId:       $payload['merchant_order_id'],
                status:        $this->statusMap[$payload['status']] ?? PaymentStatus::Failed,
                amount:        0,
                currency:      '',
            );
        }

        return new CallbackPayloadDTO(
            transactionId: $payload['id'],
            orderId:       $payload['order'],
            status:        $this->stateMap[$payload['state']] ?? PaymentStatus::Failed,
            amount:        0,
            currency:      '',
        );
    }
}
