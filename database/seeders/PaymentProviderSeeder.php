<?php

namespace Database\Seeders;

use App\PaymentSystem\Models\PaymentProvider;
use Illuminate\Database\Seeder;

class PaymentProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['slug' => 'paygate_a', 'name' => 'PayGate A'],
            ['slug' => 'paygate_b', 'name' => 'PayGate B'],
        ];

        foreach ($providers as $provider) {
            PaymentProvider::firstOrCreate(['slug' => $provider['slug']], $provider);
        }
    }
}
