<?php

namespace App\PaymentSystem\Providers;

use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Mappers\PayGateBMapper;
class PayGateBProvider implements PaymentProviderInterface
{
    public function __construct(private readonly PayGateBMapper $mapper) {}

    public function createPayment(CreatePaymentDTO $dto): PaymentResponseDTO
    {
        // stub: replace with real HTTP call to POST /external/paygate-b/payments
        $request = $this->mapper->toProviderRequest($dto);

        $id = 'b-' . random_int(100, 999999);

        $stubResponse = [
            'id'           => $id,
            'redirect_url' => 'https://paygate-b.test/checkout/' . $id,
            'state'        => 'created',
        ];

        return $this->mapper->fromProviderResponse($stubResponse);
    }

    public function validateCallback(array $payload): bool
    {
        return isset($payload['id'], $payload['order'], $payload['state']);
    }

    public function handleCallback(array $payload): CallbackPayloadDTO
    {
        return $this->mapper->fromCallback($payload);
    }

    public function externalRequestRules(): array
    {
        return [
            'order'         => ['required', 'string', 'max:64'],
            'total'         => ['required', 'integer', 'min:1'],
            'currency_code' => ['required', 'string', 'size:3'],
            'note'          => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function parseExternalRequest(array $data): CreatePaymentDTO
    {
        return new CreatePaymentDTO(
            orderId:     $data['order'],
            amount:      $data['total'] / 100,
            currency:    strtoupper($data['currency_code']),
            description: $data['note'] ?? '',
        );
    }

    public function formatExternalResponse(PaymentResponseDTO $response): array
    {
        return [
            'id'           => $response->transactionId,
            'redirect_url' => $response->redirectUrl,
            'state'        => 'created',
        ];
    }
}
