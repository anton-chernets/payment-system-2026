<?php

namespace App\PaymentSystem\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogIncomingRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::channel('payments')->info($request->route()->getActionName(), $request->all());

        return $next($request);
    }
}
