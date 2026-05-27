<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['MA', 'Morocco',         '🇲🇦', 'MAD', 30,  65, 72],
            ['SN', 'Senegal',         '🇸🇳', 'XOF', 3.5, 60, 68],
            ['CI', 'Côte d\'Ivoire',  '🇨🇮', 'XOF', 4,   60, 65],
            ['CM', 'Cameroon',        '🇨🇲', 'XAF', 3,   55, 60],
            ['NG', 'Nigeria',         '🇳🇬', 'NGN', 5,   58, 62],
            ['KE', 'Kenya',           '🇰🇪', 'KES', 4,   62, 67],
            ['ZA', 'South Africa',    '🇿🇦', 'ZAR', 6,   65, 70],
            ['EG', 'Egypt',           '🇪🇬', 'EGP', 3.5, 60, 65],
            ['GH', 'Ghana',           '🇬🇭', 'GHS', 4,   58, 63],
            ['DZ', 'Algeria',         '🇩🇿', 'DZD', 3,   60, 65],
            ['TN', 'Tunisia',         '🇹🇳', 'TND', 4,   65, 70],
            ['BF', 'Burkina Faso',    '🇧🇫', 'XOF', 3.5, 55, 60],
            ['ML', 'Mali',            '🇲🇱', 'XOF', 4,   55, 58],
            ['BJ', 'Benin',           '🇧🇯', 'XOF', 4,   58, 62],
            ['TG', 'Togo',            '🇹🇬', 'XOF', 3.5, 60, 65],
        ];

        foreach ($countries as $i => [$code, $name, $flag, $cur, $delivery, $conf, $deliv]) {
            $currency = Currency::where('code', $cur)->first();
            Country::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'flag_emoji' => $flag,
                    'currency_id' => $currency?->id,
                    'avg_delivery_cost' => $delivery,
                    'avg_confirmation_rate' => $conf,
                    'avg_delivery_rate' => $deliv,
                    'active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }
}
