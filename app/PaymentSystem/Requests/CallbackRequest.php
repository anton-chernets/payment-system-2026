<?php

namespace App\PaymentSystem\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return match ($this->route('provider')) {
            'paygate-a' => $this->has('payment_id') ? [
                'payment_id'        => ['required', 'string'],
                'merchant_order_id' => ['required', 'string'],
                'status'            => ['required', 'string', Rule::in(['new', 'paid', 'rejected'])],
            ] : [
                'id'    => ['required', 'string'],
                'order' => ['required', 'string'],
                'state' => ['required', 'string', Rule::in(['created', 'success', 'error'])],
            ],
            'paygate-b' => [
                'id'    => ['required', 'string'],
                'order' => ['required', 'string'],
                'state' => ['required', 'string', Rule::in(['created', 'success', 'error'])],
            ],
            default => [],
        };
    }
}
