<?php

use App\PaymentSystem\Controllers\CallbackController;
use App\PaymentSystem\Controllers\PaymentController;
use App\PaymentSystem\Middleware\ResolvePaymentProvider;
use Illuminate\Support\Facades\Route;

Route::post('/payments', [PaymentController::class, 'create']);

Route::post('/external/paygate-a/create', [PaymentController::class, 'createExternal'])
    ->defaults('provider', 'paygate-a');
Route::post('/external/paygate-b/payments', [PaymentController::class, 'createExternal'])
    ->defaults('provider', 'paygate-b');

Route::post('/callbacks/{provider}', CallbackController::class)
    ->middleware(ResolvePaymentProvider::class);
