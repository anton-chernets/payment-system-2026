<?php

namespace App\PaymentSystem\Requests;

use App\PaymentSystem\Enums\PaymentProviderSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return match (PaymentProviderSlug::from($this->route('provider'))) {
            PaymentProviderSlug::PaygateA => [
                'payment_id'        => ['required', 'string'],
                'merchant_order_id' => ['required', 'string'],
                'status'            => ['required', 'string', Rule::in(['new', 'paid', 'rejected'])],
            ],
            PaymentProviderSlug::PaygateB => [
                'id'    => ['required', 'string'],
                'order' => ['required', 'string'],
                'state' => ['required', 'string', Rule::in(['created', 'success', 'error'])],
            ],
        };
    }
}
