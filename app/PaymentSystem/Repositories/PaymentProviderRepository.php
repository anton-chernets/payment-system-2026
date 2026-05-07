<?php

namespace App\PaymentSystem\Repositories;

use App\PaymentSystem\Models\PaymentProvider;
use Illuminate\Support\Collection;

class PaymentProviderRepository
{
    public function findBySlugOrFail(string $slug): PaymentProvider
    {
        return PaymentProvider::where('slug', str_replace('-', '_', $slug))->firstOrFail();
    }

    public function allSlugs(): Collection
    {
        return PaymentProvider::pluck('slug');
    }
}
