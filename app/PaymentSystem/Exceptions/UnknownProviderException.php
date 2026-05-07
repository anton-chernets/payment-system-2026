<?php

namespace App\PaymentSystem\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class UnknownProviderException extends RuntimeException
{
    public function __construct(string $provider)
    {
        parent::__construct("Unknown payment provider: {$provider}");
    }

    public function render(): JsonResponse
    {
        return response()->json(['error' => 'Unknown provider'], 400);
    }
}
