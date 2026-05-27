<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate_to_usd' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate_to_usd' => 1.08],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'DH', 'rate_to_usd' => 0.10],
            ['code' => 'XOF', 'name' => 'West African CFA', 'symbol' => 'CFA', 'rate_to_usd' => 0.0017],
            ['code' => 'XAF', 'name' => 'Central African CFA', 'symbol' => 'FCFA', 'rate_to_usd' => 0.0017],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'rate_to_usd' => 0.0011],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate_to_usd' => 0.0078],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'rate_to_usd' => 0.055],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'rate_to_usd' => 0.020],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'rate_to_usd' => 0.07],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'DA', 'rate_to_usd' => 0.0074],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'TND', 'rate_to_usd' => 0.32],
        ];

        foreach ($currencies as $c) {
            Currency::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
