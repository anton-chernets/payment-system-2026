<?php

namespace App\PaymentSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = ['code', 'name'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'currency_id');
    }
}
