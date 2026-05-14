<?php

namespace App\PaymentSystem\Middleware;

use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\Enums\PaymentProviderSlug;
use App\PaymentSystem\PaymentProviderFactory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePaymentProvider
{
    public function __construct(private readonly PaymentProviderFactory $factory) {}

    public function handle(Request $request, Closure $next): Response
    {
        $slug = PaymentProviderSlug::tryFrom($request->route('provider'));

        if (!$slug) {
            abort(404);
        }

        app()->instance(PaymentProviderInterface::class, $this->factory->make($slug->value));

        return $next($request);
    }
}
