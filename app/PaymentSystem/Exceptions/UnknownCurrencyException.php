<?php

namespace App\PaymentSystem\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class UnknownCurrencyException extends RuntimeException
{
    public function __construct(string $code)
    {
        parent::__construct("Unknown currency code: {$code}");
    }

    public function render(): JsonResponse
    {
        return response()->json(['error' => 'Unknown currency code'], 422);
    }
}
