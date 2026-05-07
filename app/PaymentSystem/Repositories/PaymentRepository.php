<?php

namespace App\PaymentSystem\Repositories;

use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Models\Currency;
use App\PaymentSystem\Models\Payment;
use App\PaymentSystem\Models\PaymentProvider;
use Illuminate\Support\Str;

class PaymentRepository
{
    public function findByOrderId(string $orderId): ?Payment
    {
        return Payment::where('order_id', $orderId)->first();
    }

    public function findByOrderIdOrFail(string $orderId): Payment
    {
        return Payment::where('order_id', $orderId)->firstOrFail();
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        if (!Str::isUuid($transactionId)) {
            return null;
        }

        return Payment::where('transaction_id', $transactionId)->first();
    }

    public function findForCallback(string $orderId, string $transactionId): ?Payment
    {
        if (!Str::isUuid($transactionId)) {
            return null;
        }

        return Payment::where('order_id', $orderId)
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function createFromDTOs(
        CreatePaymentDTO $dto,
        PaymentResponseDTO $response,
        PaymentProvider $provider,
        Currency $currency,
    ): Payment {
        return Payment::create([
            'order_id'       => $dto->orderId,
            'provider_id'    => $provider->id,
            'currency_id'    => $currency->id,
            'transaction_id' => $response->transactionId,
            'status'         => $response->status->value,
            'amount'         => (int) round($dto->amount * 100),
            'redirect_url'   => url('/api/callbacks/' . str_replace('_', '-', $provider->slug)),
        ]);
    }
}
