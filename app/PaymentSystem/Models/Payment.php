<?php

namespace App\PaymentSystem\Models;

use App\PaymentSystem\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider_id',
        'currency_id',
        'transaction_id',
        'status',
        'amount',
        'redirect_url',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'provider_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
