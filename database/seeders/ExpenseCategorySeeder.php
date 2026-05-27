<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Ads',           'slug' => 'ads',           'color' => '#6366f1', 'icon' => 'heroicon-o-megaphone'],
            ['name' => 'Salaries',      'slug' => 'salaries',      'color' => '#10b981', 'icon' => 'heroicon-o-user-group'],
            ['name' => 'Logistics',     'slug' => 'logistics',     'color' => '#f59e0b', 'icon' => 'heroicon-o-truck'],
            ['name' => 'Rent',          'slug' => 'rent',          'color' => '#8b5cf6', 'icon' => 'heroicon-o-home'],
            ['name' => 'Software',      'slug' => 'software',      'color' => '#0ea5e9', 'icon' => 'heroicon-o-computer-desktop'],
            ['name' => 'Suppliers',     'slug' => 'suppliers',     'color' => '#ec4899', 'icon' => 'heroicon-o-building-storefront'],
            ['name' => 'Refunds',       'slug' => 'refunds',       'color' => '#ef4444', 'icon' => 'heroicon-o-arrow-uturn-left'],
            ['name' => 'Miscellaneous', 'slug' => 'miscellaneous', 'color' => '#64748b', 'icon' => 'heroicon-o-ellipsis-horizontal-circle'],
        ];

        foreach ($categories as $c) {
            ExpenseCategory::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }
}
