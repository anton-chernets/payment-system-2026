<?php

namespace App\PaymentSystem\Contracts;

use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;

interface PaymentProviderInterface
{
    public function createPayment(CreatePaymentDTO $dto): PaymentResponseDTO;

    public function validateCallback(array $payload): bool;

    public function handleCallback(array $payload): CallbackPayloadDTO;

    public function externalRequestRules(): array;

    public function parseExternalRequest(array $data): CreatePaymentDTO;

    public function formatExternalResponse(PaymentResponseDTO $response): array;
}
