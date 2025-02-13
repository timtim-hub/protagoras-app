<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MainSetting;

class MainSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default settings if they don't exist
        if (!MainSetting::exists()) {
            MainSetting::create([
                'languages' => json_encode(['en' => 'English']), // Default language
                'default_language' => 'en',
            ]);
        }
    }
} 