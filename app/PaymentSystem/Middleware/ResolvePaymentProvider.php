<?php

namespace App\PaymentSystem\Middleware;

use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\PaymentProviderFactory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePaymentProvider
{
    public function __construct(private readonly PaymentProviderFactory $factory) {}

    public function handle(Request $request, Closure $next): Response
    {
        $provider = $this->factory->make($request->route('provider'));

        app()->instance(PaymentProviderInterface::class, $provider);

        return $next($request);
    }
}
