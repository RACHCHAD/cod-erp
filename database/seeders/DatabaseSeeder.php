<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            PlatformSeeder::class,
            ExpenseCategorySeeder::class,
            DemoSeeder::class,
        ]);
    }
}
