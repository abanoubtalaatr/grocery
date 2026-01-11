<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SubcategorySeeder::class,
            MealSeeder::class,
            FaqSeeder::class,
            StaticPageSeeder::class,
            ContactMessageSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
