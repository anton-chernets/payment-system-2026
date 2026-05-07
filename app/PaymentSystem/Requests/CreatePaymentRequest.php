<?php

namespace App\PaymentSystem\Requests;

use App\PaymentSystem\Repositories\PaymentProviderRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        $slugs = app(PaymentProviderRepository::class)->allSlugs();

        return [
            'provider'      => ['required', 'string', Rule::in($slugs)],
            'order_id'      => ['required', 'string', 'max:64'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'currency'      => ['required', 'string', 'size:3'],
            'description'   => ['sometimes', 'string', 'max:255'],
        ];
    }
}
