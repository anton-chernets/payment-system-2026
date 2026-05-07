<?php

namespace App\PaymentSystem\Resources;

use App\PaymentSystem\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function __construct(
        Payment $resource,
        private readonly string $externalId,
        private readonly string $paymentUrl,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'provider'    => $this->provider->slug,
            'external_id' => $this->externalId,
            'status'      => $this->status->value,
            'payment_url' => $this->paymentUrl,
        ];
    }
}
