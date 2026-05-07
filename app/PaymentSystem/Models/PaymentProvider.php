<?php

namespace App\PaymentSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentProvider extends Model
{
    protected $fillable = ['slug', 'name'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'provider_id');
    }
}
