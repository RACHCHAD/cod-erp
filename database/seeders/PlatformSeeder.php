<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['name' => 'Facebook',  'slug' => 'facebook',  'color' => '#1877F2', 'icon' => 'heroicon-o-globe-alt'],
            ['name' => 'TikTok',    'slug' => 'tiktok',    'color' => '#000000', 'icon' => 'heroicon-o-musical-note'],
            ['name' => 'Google',    'slug' => 'google',    'color' => '#4285F4', 'icon' => 'heroicon-o-magnifying-glass'],
            ['name' => 'Snapchat',  'slug' => 'snapchat',  'color' => '#FFFC00', 'icon' => 'heroicon-o-camera'],
            ['name' => 'Instagram', 'slug' => 'instagram', 'color' => '#E1306C', 'icon' => 'heroicon-o-photo'],
            ['name' => 'YouTube',   'slug' => 'youtube',   'color' => '#FF0000', 'icon' => 'heroicon-o-play'],
        ];

        foreach ($platforms as $p) {
            Platform::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
