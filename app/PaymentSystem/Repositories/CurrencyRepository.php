<?php

namespace App\PaymentSystem\Repositories;

use App\PaymentSystem\Exceptions\UnknownCurrencyException;
use App\PaymentSystem\Models\Currency;
use Illuminate\Support\Collection;

class CurrencyRepository
{
    public function findByCodeOrFail(string $code): Currency
    {
        return Currency::where('code', $code)->first()
            ?? throw new UnknownCurrencyException($code);
    }

    public function allCodes(): Collection
    {
        return Currency::pluck('code');
    }
}
