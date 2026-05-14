<?php

namespace App\PaymentSystem\Providers;

use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Mappers\PayGateAMapper;
class PayGateAProvider implements PaymentProviderInterface
{
    public function __construct(private readonly PayGateAMapper $mapper) {}

    public function createPayment(CreatePaymentDTO $dto): PaymentResponseDTO
    {
        // stub: replace with real HTTP POST to /external/paygate-a/create
        $request = $this->mapper->toProviderRequest($dto);

        $paymentId = 'a-' . random_int(100000, 999999);

        $stubResponse = [
            'payment_id'  => $paymentId,
            'payment_url' => 'https://paygate-a.test/pay/' . $paymentId,
            'status'      => 'new',
        ];

        return $this->mapper->fromProviderResponse($stubResponse);
    }

    public function validateCallback(array $payload): bool
    {
        return isset($payload['payment_id'], $payload['merchant_order_id'], $payload['status']);
    }

    public function handleCallback(array $payload): CallbackPayloadDTO
    {
        return $this->mapper->fromCallback($payload);
    }

    public function externalRequestRules(): array
    {
        return [
            'merchant_order_id' => ['required', 'string', 'max:64'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'currency'          => ['required', 'string', 'size:3'],
        ];
    }

    public function parseExternalRequest(array $data): CreatePaymentDTO
    {
        return new CreatePaymentDTO(
            orderId:  $data['merchant_order_id'],
            amount:   (float) $data['amount'],
            currency: strtoupper($data['currency']),
        );
    }

    public function formatExternalResponse(PaymentResponseDTO $response): array
    {
        return [
            'payment_id'  => $response->transactionId,
            'payment_url' => $response->redirectUrl,
            'status'      => 'new',
        ];
    }
}
