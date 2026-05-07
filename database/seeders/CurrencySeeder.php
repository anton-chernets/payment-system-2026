<?php

namespace Database\Seeders;

use App\PaymentSystem\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'name' => 'Euro'],
            ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia'],
            ['code' => 'GBP', 'name' => 'British Pound'],
            ['code' => 'PLN', 'name' => 'Polish Zloty'],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
