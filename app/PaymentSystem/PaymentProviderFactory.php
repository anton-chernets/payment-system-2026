<?php

namespace App\PaymentSystem;

use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\Exceptions\UnknownProviderException;
use App\PaymentSystem\Providers\PayGateAProvider;
use App\PaymentSystem\Providers\PayGateBProvider;

class PaymentProviderFactory
{
    private array $map = [
        'paygate_a' => PayGateAProvider::class,
        'paygate_b' => PayGateBProvider::class,
    ];

    public function make(string $provider): PaymentProviderInterface
    {
        $key = str_replace('-', '_', $provider);

        if (!isset($this->map[$key])) {
            throw new UnknownProviderException($provider);
        }

        return app($this->map[$key]);
    }
}
