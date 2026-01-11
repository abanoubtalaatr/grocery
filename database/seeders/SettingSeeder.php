<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = Setting::first();
        
        if (!$settings) {
            
            $settings = Setting::create([
                'facebook' => 'https://facebook.com/yourpage',
                'linkedin' => 'https://linkedin.com/company/yourcompany',
                'instagram' => 'https://instagram.com/yourprofile',
                'twitter' => 'https://twitter.com/yourhandle',
                'email' => 'contact@example.com',
                'phone' => '+1 (234) 567-8900',
                'address' => '123 Main Street, City, Country',
                'site_name' => 'Your Site Name',
                'site_description' => 'Your site description goes here',
                'copyright_text' => '© ' . date('Y') . ' Your Company. All rights reserved.',
            ]);
        }
        $settings= Setting::where('id', $settings->id)->update([                'facebook' => 'https://facebook.com/yourpage',
        'linkedin' => 'https://linkedin.com/company/yourcompany',
        'instagram' => 'https://instagram.com/yourprofile',
        'twitter' => 'https://twitter.com/yourhandle',
        'email' => 'contact@example.com',
        'phone' => '+1 (234) 567-8900',
        'address' => '123 Main Street, City, Country',
        'site_name' => 'Your Site Name',
        'site_description' => 'Your site description goes here',
        'copyright_text' => '© ' . date('Y') . ' Your Company. All rights reserved.',]);
        
    }
}