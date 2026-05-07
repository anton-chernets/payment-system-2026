<?php

namespace App\PaymentSystem\Requests;

use App\PaymentSystem\PaymentProviderFactory;
use Illuminate\Foundation\Http\FormRequest;

class ExternalCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return app(PaymentProviderFactory::class)
            ->make($this->route('provider'))
            ->externalRequestRules();
    }
}
